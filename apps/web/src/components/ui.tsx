import type { ButtonHTMLAttributes, HTMLAttributes, ReactNode } from "react";
import { cn } from "@/lib/utils";

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn("rounded-[var(--radius)] border border-[var(--line)] bg-[var(--surface)] shadow-[0_1px_2px_rgba(13,40,51,.03)]", className)} {...props} />;
}

export function Button({ className, variant = "primary", ...props }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: "primary" | "secondary" | "ghost" }) {
  return <button className={cn("inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[var(--sea)] focus:ring-offset-2 disabled:opacity-50", variant === "primary" && "bg-[var(--navy)] text-white hover:bg-[var(--navy-soft)]", variant === "secondary" && "border border-[var(--line)] bg-white text-[var(--ink)] hover:bg-[var(--surface-muted)]", variant === "ghost" && "text-[var(--muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--ink)]", className)} {...props} />;
}

export function Badge({ children, tone = "default" }: { children: ReactNode; tone?: "default" | "success" | "warning" | "danger" | "info" }) {
  const tones = { default: "bg-slate-100 text-slate-600", success: "bg-emerald-50 text-emerald-700", warning: "bg-amber-50 text-amber-700", danger: "bg-red-50 text-red-700", info: "bg-sky-50 text-sky-700" };
  return <span className={cn("inline-flex rounded-full px-2.5 py-1 text-xs font-semibold", tones[tone])}>{children}</span>;
}

export function Avatar({ initials, className }: { initials: string; className?: string }) {
  return <span className={cn("inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[var(--sea-soft)] text-xs font-bold text-[var(--sea)]", className)}>{initials}</span>;
}

export function Progress({ value, tone = "sea" }: { value: number; tone?: "sea" | "warning" | "danger" }) {
  return <div className="h-2 overflow-hidden rounded-full bg-slate-100"><div className={cn("h-full rounded-full", tone === "sea" && "bg-[var(--sea)]", tone === "warning" && "bg-[var(--warning)]", tone === "danger" && "bg-[var(--danger)]")} style={{ width: `${value}%` }} /></div>;
}

export function Field({ label, children }: { label: string; children: ReactNode }) {
  return <label className="grid gap-2 text-sm font-semibold text-slate-700"><span>{label}</span>{children}</label>;
}

export const inputClass = "h-11 w-full rounded-xl border border-[var(--line)] bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[var(--sea)] focus:ring-3 focus:ring-[var(--sea-soft)]";
