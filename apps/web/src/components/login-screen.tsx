"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import * as Icons from "lucide-react";
import type { ScreenDefinition } from "@/data/screens";
import { Badge, Button, Field, inputClass } from "@/components/ui";
import { useAuth } from "@/lib/auth/auth-provider";
import { ApiError } from "@/lib/api/client";

export function LoginScreen({ screen }: { screen: ScreenDefinition }) {
  const { login, status } = useAuth();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [remember, setRemember] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const submittingRef = useRef(false);
  const [error, setError] = useState("");
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const isLogin = screen.path === "/login";
  useEffect(() => { if (status === "authenticated" && isLogin) router.replace("/dashboard"); }, [status, isLogin, router]);
  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (submittingRef.current || !isLogin || status === "loading") return;
    submittingRef.current = true;
    setSubmitting(true);
    setError("");
    setErrors({});
    try { await login(email, password, remember); }
    catch (cause) {
      setError(cause instanceof ApiError ? cause.message : "Oturum kaydedilemedi. Tarayıcı depolama iznini kontrol edip tekrar deneyin.");
      setErrors(cause instanceof ApiError ? cause.errors : {});
    } finally { submittingRef.current = false; setSubmitting(false); }
  }
  return <div className="grid min-h-screen bg-white lg:grid-cols-[1fr_1.05fr]"><div className="flex items-center justify-center p-6"><div className="w-full max-w-md"><Link href="/dashboard" className="mb-12 flex items-center gap-3"><span className="grid size-11 place-items-center rounded-2xl bg-[var(--navy)] text-white"><Icons.Sailboat className="size-5" /></span><span className="text-xl font-bold">YachtOS</span></Link><h1 className="text-3xl font-bold tracking-[-.04em]">{screen.title}</h1><p className="mt-2 text-[var(--muted)]">Yat operasyonlarınızı tek merkezden yönetin.</p><form className="mt-8 space-y-5" onSubmit={submit} aria-busy={submitting}><Field label="E-posta adresi"><input type="email" className={inputClass} name="email" autoComplete="username" required value={email} onChange={e => setEmail(e.target.value)} disabled={!isLogin || submitting} aria-invalid={Boolean(errors.email)} aria-describedby={errors.email ? "email-error" : undefined} />{errors.email && <span id="email-error" className="text-xs text-red-600">{errors.email.join(" ")}</span>}</Field><Field label="Şifre"><input type="password" className={inputClass} name="password" autoComplete="current-password" required value={password} onChange={e => setPassword(e.target.value)} disabled={!isLogin || submitting} aria-invalid={Boolean(errors.password)} aria-describedby={errors.password ? "password-error" : undefined} />{errors.password && <span id="password-error" className="text-xs text-red-600">{errors.password.join(" ")}</span>}</Field><div className="flex items-center justify-between text-sm"><label className="flex items-center gap-2"><input type="checkbox" checked={remember} onChange={e => setRemember(e.target.checked)} disabled={submitting} />Beni hatırla</label><Link href="/forgot-password" className="font-semibold text-[var(--sea)]">Şifremi unuttum</Link></div>{error && <p role="alert" className="text-sm text-red-600">{error}</p>}{!isLogin && <p className="text-sm text-[var(--muted)]">Bu akış henüz kullanıma açık değil. <Link href="/login" className="text-[var(--sea)]">Girişe dön</Link></p>}<Button className="w-full" type="submit" disabled={!isLogin || submitting || status === "loading" || status === "authenticated"}>{submitting ? "Giriş yapılıyor…" : status === "loading" ? "Oturum kontrol ediliyor…" : "Giriş yap"}<Icons.ArrowRight className="size-4" /></Button></form><p className="mt-8 text-center text-xs text-[var(--muted)]">YachtOS 2026</p></div></div><div className="relative hidden overflow-hidden bg-[var(--navy)] p-12 text-white lg:flex lg:flex-col lg:justify-between"><div className="absolute -right-20 -top-20 size-[420px] rounded-full bg-[var(--sea)]/15 blur-2xl" /><div className="relative flex justify-end"><Badge tone="success">Operational clarity</Badge></div><div className="relative max-w-xl"><div className="mb-6 grid size-14 place-items-center rounded-2xl bg-white/10"><Icons.Compass className="size-7 text-[var(--sea)]" /></div><h2 className="text-4xl font-semibold leading-tight tracking-[-.04em]">Every detail on board.<br/>One clear view.</h2><p className="mt-5 max-w-md leading-7 text-white/55">Bakım, ekip, görevler, stok ve finans. Profesyonel yat operasyonlarının tamamı tek çalışma alanında.</p></div><div className="relative grid grid-cols-3 gap-3">{[["18","Aktif görev"],["6","Ekip üyesi"],["98%","Hazırlık"]].map(x=><div className="rounded-2xl border border-white/10 bg-white/5 p-4" key={x[1]}><b className="text-xl">{x[0]}</b><span className="mt-1 block text-xs text-white/45">{x[1]}</span></div>)}</div></div></div>;
}
