export type User = { id: string; first_name: string; last_name: string; email: string };
export type Session = { access_token: string; expires_at: string; user: User };
export type Tenant = {
  id: string; name: string; slug: string; type: string; status: string;
  country: string | null; timezone: string; currency: string;
};
export const SESSION_KEY = "yachtos.auth.v1";

export function parseSession(raw: string | null): Session | null {
  try {
    const value = JSON.parse(raw ?? "null");
    if (!value || typeof value.access_token !== "string" || !value.access_token
      || typeof value.expires_at !== "string" || !(Date.parse(value.expires_at) > Date.now())
      || !value.user || !["id", "first_name", "last_name", "email"].every(key => typeof value.user[key] === "string")) return null;
    return { access_token: value.access_token, expires_at: value.expires_at, user: {
      id: value.user.id, first_name: value.user.first_name, last_name: value.user.last_name, email: value.user.email,
    } };
  } catch {
    return null;
  }
}

export function clearSession() {
  // Logout must clear in-memory state even if browser storage is unavailable.
  for (const name of ["localStorage", "sessionStorage"] as const) {
    try { window[name].removeItem(SESSION_KEY); } catch { /* Storage may be disabled. */ }
  }
}

export function readSession(): Session | null {
  for (const name of ["sessionStorage", "localStorage"] as const) {
    try {
      const session = parseSession(window[name].getItem(SESSION_KEY));
      if (session) return session;
    } catch { /* One blocked storage must not hide a session in the other. */ }
  }
  clearSession();
  return null;
}

export function saveSession(session: Session, remember: boolean) {
  clearSession();
  (remember ? window.localStorage : window.sessionStorage).setItem(SESSION_KEY, JSON.stringify(session));
}
