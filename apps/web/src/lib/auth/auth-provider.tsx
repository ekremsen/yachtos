"use client";

import { createContext, useCallback, useContext, useEffect, useRef, useState } from "react";
import { usePathname, useRouter } from "next/navigation";
import { ApiError, apiRequest } from "@/lib/api/client";
import { clearSelectedYacht, clearSession, readSelectedYacht, readSession, saveSelectedYacht, saveSession, SESSION_KEY, type Session, type Tenant, type Yacht } from "./session";

type Status = "loading" | "anonymous" | "authenticated" | "error";
type Auth = {
  status: Status; session: Session | null; tenant: Tenant | null; yachts: Yacht[]; activeYacht: Yacht | null; yachtSelectionRequired: boolean;
  selectYacht: (id: string) => Promise<void>;
  request: <T>(path: string, options?: { method?: "GET" | "POST" | "PATCH"; body?: unknown }) => Promise<T>;
  login: (email: string, password: string, remember: boolean) => Promise<void>;
  logout: () => Promise<void>; restore: () => Promise<void>;
};
const AuthContext = createContext<Auth | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [status, setStatus] = useState<Status>("loading");
  const [session, setSession] = useState<Session | null>(null);
  const [tenant, setTenant] = useState<Tenant | null>(null);
  const [yachts, setYachts] = useState<Yacht[]>([]);
  const [activeYacht, setActiveYacht] = useState<Yacht | null>(null);
  const [yachtSelectionRequired, setYachtSelectionRequired] = useState(false);
  const generation = useRef(0);
  const loginPending = useRef(false);
  const router = useRouter();

  const clear = useCallback(() => {
    generation.current++;
    clearSession();
    setSession(null);
    setTenant(null);
    setYachts([]);
    setActiveYacht(null);
    setYachtSelectionRequired(false);
    clearSelectedYacht();
    setStatus("anonymous");
  }, []);

  const restore = useCallback(async () => {
    if (loginPending.current) return;
    const attempt = ++generation.current;
    const saved = readSession();
    if (!saved) { clear(); return; }
    setStatus("loading");
    try {
      const response = await apiRequest<{ data: Tenant }>("/tenant", { token: saved.access_token });
      if (attempt !== generation.current) return;
      const list = await apiRequest<{ data: Yacht[] }>("/yachts", { token: saved.access_token });
      if (attempt !== generation.current) return;
      const yachtState = await resolveYacht(saved.access_token, list.data);
      if (attempt !== generation.current) return;
      setSession(saved);
      setTenant(response.data);
      setYachts(yachtState.yachts);
      setActiveYacht(yachtState.activeYacht);
      setYachtSelectionRequired(yachtState.selectionRequired);
      setStatus("authenticated");
    } catch (error) {
      if (attempt !== generation.current) return;
      if (error instanceof ApiError && [401, 403].includes(error.status)) clear();
      else setStatus("error");
    }
  }, [clear]);

  async function resolveYacht(token: string, list: Yacht[]) {
    const selectedId = readSelectedYacht();
    if (selectedId) {
      try {
        const selected = await apiRequest<{ data: Yacht }>("/yacht", { token, yachtId: selectedId });
        return { yachts: list, activeYacht: selected.data, selectionRequired: false };
      } catch (error) {
        if (!(error instanceof ApiError) || error.status !== 403) throw error;
        clearSelectedYacht();
      }
    }
    if (list.length === 1) {
      const resolved = await apiRequest<{ data: Yacht }>("/yacht", { token });
      return { yachts: list, activeYacht: resolved.data, selectionRequired: false };
    } else {
      return { yachts: list, activeYacht: null, selectionRequired: list.length > 1 };
    }
  }

  async function selectYacht(id: string) {
    if (!session) return;
    const resolved = await apiRequest<{ data: Yacht }>("/yacht", { token: session.access_token, yachtId: id });
    saveSelectedYacht(resolved.data.id);
    setActiveYacht(resolved.data);
    setYachtSelectionRequired(false);
  }

  const request = useCallback(async function requestWithActiveYacht<T>(path: string, options: { method?: "GET" | "POST" | "PATCH"; body?: unknown } = {}) {
    if (!session || !activeYacht) throw new Error("Select an active yacht first.");
    return apiRequest<T>(path, { ...options, token: session.access_token, yachtId: activeYacht.id });
  }, [session, activeYacht]);

  useEffect(() => {
    void restore();
    const focus = () => { if (document.visibilityState === "visible") void restore(); };
    const storage = (event: StorageEvent) => { if (event.key === SESSION_KEY || event.key === null) void restore(); };
    window.addEventListener("focus", focus);
    document.addEventListener("visibilitychange", focus);
    window.addEventListener("storage", storage);
    return () => {
      generation.current++;
      window.removeEventListener("focus", focus);
      document.removeEventListener("visibilitychange", focus);
      window.removeEventListener("storage", storage);
    };
  }, [restore]);

  useEffect(() => {
    if (!session) return;
    const expire = () => { if (Date.parse(session.expires_at) <= Date.now()) clear(); };
    const timer = window.setInterval(expire, 1000);
    return () => window.clearInterval(timer);
  }, [session, clear]);

  async function login(email: string, password: string, remember: boolean) {
    if (loginPending.current) return;
    loginPending.current = true;
    const attempt = ++generation.current;
    let issued: Session | undefined;
    try {
      const response = await apiRequest<{ data: Session }>("/auth/login", { method: "POST", body: { email, password } });
      issued = { access_token: response.data.access_token, expires_at: response.data.expires_at, user: response.data.user };
      const resolved = await apiRequest<{ data: Tenant }>("/tenant", { token: issued.access_token });
      const yachtList = await apiRequest<{ data: Yacht[] }>("/yachts", { token: issued.access_token });
      const yachtState = await resolveYacht(issued.access_token, yachtList.data);
      if (attempt !== generation.current) throw new Error("Oturum değişti. Lütfen tekrar giriş yapın.");
      saveSession(issued, remember);
      setSession(issued);
      setTenant(resolved.data);
      setYachts(yachtState.yachts);
      setActiveYacht(yachtState.activeYacht);
      setYachtSelectionRequired(yachtState.selectionRequired);
      setStatus("authenticated");
      router.replace("/dashboard");
    } catch (error) {
      if (issued) void apiRequest("/auth/logout", { method: "POST", token: issued.access_token }).catch(() => {});
      if (attempt === generation.current) clear();
      throw error;
    } finally {
      loginPending.current = false;
    }
  }

  async function logout() {
    const token = session?.access_token ?? readSession()?.access_token;
    clear();
    router.replace("/login");
    if (token) await apiRequest("/auth/logout", { method: "POST", token }).catch(() => {});
  }

  return <AuthContext.Provider value={{ status, session, tenant, yachts, activeYacht, yachtSelectionRequired, selectYacht, request, login, logout, restore }}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const auth = useContext(AuthContext);
  if (!auth) throw new Error("AuthProvider is required.");
  return auth;
}

export function RequireAuth({ children }: { children: React.ReactNode }) {
  const { status, restore, logout } = useAuth();
  const router = useRouter();
  const pathname = usePathname();
  useEffect(() => { void restore(); }, [pathname, restore]);
  useEffect(() => { if (status === "anonymous") router.replace("/login"); }, [status, router]);
  if (status === "authenticated") return <>{children}</>;
  return <div className="grid min-h-screen place-items-center bg-[var(--background)] p-6"><div className="space-y-4 text-center" role="status">
    <p>{status === "error" ? "Oturum doğrulanamadı. Bağlantınızı kontrol edin." : "Oturum kontrol ediliyor…"}</p>
    {status === "error" && <><button className="mr-4 text-[var(--sea)]" onClick={() => void restore()}>Tekrar dene</button><button onClick={() => void logout()}>Çıkış yap</button></>}
  </div></div>;
}
