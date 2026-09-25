"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState } from "react";
import * as Icons from "lucide-react";
import { navigation, screenCount } from "@/data/screens";
import { Avatar, Badge, Button } from "@/components/ui";
import { RequireAuth, useAuth } from "@/lib/auth/auth-provider";
import { cn } from "@/lib/utils";

const iconMap: Record<string, Icons.LucideIcon> = {
  LayoutDashboard: Icons.LayoutDashboard, Ship: Icons.Ship, ListChecks: Icons.ListChecks,
  Wrench: Icons.Wrench, Users: Icons.Users, Boxes: Icons.Boxes, ShoppingCart: Icons.ShoppingCart,
  WalletCards: Icons.WalletCards, Files: Icons.Files, Route: Icons.Route,
  ChartNoAxesCombined: Icons.ChartNoAxesCombined, Settings: Icons.Settings,
};

export function AppShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  if (["/login", "/forgot-password", "/reset-password"].includes(pathname) || pathname.startsWith("/invitation/")) return <>{children}</>;
  return <RequireAuth><ProtectedShell>{children}</ProtectedShell></RequireAuth>;
}

function ProtectedShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const { session, tenant, logout } = useAuth();
  const user = session!.user;

  if (pathname.startsWith("/onboarding/")) return <>{children}</>;

  return (
    <div className="min-h-screen lg:grid lg:grid-cols-[252px_minmax(0,1fr)]">
      {open && <button aria-label="Menüyü kapat" className="fixed inset-0 z-30 bg-slate-950/30 lg:hidden" onClick={() => setOpen(false)} />}
      <aside className={cn("fixed inset-y-0 left-0 z-40 flex w-[252px] flex-col bg-[var(--navy)] text-white transition-transform lg:sticky lg:top-0 lg:h-screen", open ? "translate-x-0" : "-translate-x-full lg:translate-x-0")}>
        <div className="flex h-20 items-center gap-3 border-b border-white/10 px-5">
          <div className="grid size-10 place-items-center rounded-xl bg-[var(--sea)]"><Icons.Sailboat className="size-5" /></div>
          <div><div className="text-lg font-bold tracking-tight">YachtOS</div><div className="text-[10px] font-semibold tracking-[.19em] text-white/45">OPERATIONS</div></div>
          <Badge tone="info">{screenCount}</Badge>
        </div>
        <nav className="scrollbar-none flex-1 space-y-1 overflow-y-auto p-3">
          {navigation.map((item) => {
            const Icon = iconMap[item.icon];
            const active = pathname === item.href || (item.href !== "/dashboard" && pathname.startsWith(item.href.split("/").slice(0, 2).join("/")));
            return <Link key={item.href} href={item.href} onClick={() => setOpen(false)} className={cn("flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/62 transition hover:bg-white/7 hover:text-white", active && "bg-white/10 text-white shadow-[inset_3px_0_0_var(--sea)]")}><Icon className="size-[18px]" />{item.label}</Link>;
          })}
        </nav>
        <div className="border-t border-white/10 p-4">
          <div className="rounded-2xl bg-white/6 p-3"><div className="flex items-center gap-3"><div className="grid size-9 place-items-center rounded-xl bg-[var(--sea)]/20 text-[var(--sea)]"><Icons.Ship className="size-5" /></div><div><div className="text-xs text-white/45">Aktif yat</div><div className="text-sm font-semibold">M/Y Azure</div></div></div><div className="mt-3 flex items-center gap-2 text-xs text-white/55"><span className="size-2 rounded-full bg-emerald-400" />Göcek Marina</div></div>
        </div>
      </aside>
      <section className="min-w-0">
        <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-[var(--line)] bg-white/90 px-4 backdrop-blur md:px-7">
          <div className="flex items-center gap-3"><button className="grid size-10 place-items-center rounded-xl border border-[var(--line)] lg:hidden" onClick={() => setOpen(true)}><Icons.Menu className="size-5" /></button><div className="hidden items-center gap-2 text-sm text-[var(--muted)] sm:flex"><Icons.MapPin className="size-4" />Göcek Marina <span className="text-slate-300">•</span> 24°C</div></div>
          <div className="flex items-center gap-2"><button className="relative grid size-10 place-items-center rounded-xl border border-[var(--line)] bg-white"><Icons.Bell className="size-[18px]" /><span className="absolute right-2 top-2 size-2 rounded-full bg-[var(--danger)] ring-2 ring-white" /></button><select aria-label="Rol (prototip)" title="Rol yönetimi henüz uygulanmadı" value="Captain" disabled className="hidden h-10 rounded-xl border border-[var(--line)] bg-white px-3 text-sm font-semibold sm:block"><option>Owner</option><option>Captain</option><option>Engineer</option><option>Crew Member</option><option>Stewardess</option><option>Accountant</option></select><Avatar initials={`${user.first_name[0] ?? ""}${user.last_name[0] ?? ""}`} /><div className="hidden text-sm md:block"><div className="font-semibold">{user.first_name} {user.last_name}</div><div className="text-xs text-[var(--muted)]">{tenant?.name}</div></div><Button variant="ghost" aria-label="Çıkış yap" title="Çıkış yap" onClick={() => void logout()}><Icons.LogOut className="size-4" /></Button></div>
        </header>
        <main className="mx-auto max-w-[1500px] p-4 md:p-7">{children}</main>
      </section>
    </div>
  );
}
