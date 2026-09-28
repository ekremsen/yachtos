"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import * as Icons from "lucide-react";
import { useAuth } from "@/lib/auth/auth-provider";
import { ApiError } from "@/lib/api/client";
import type { ScreenDefinition } from "@/data/screens";
import { Avatar, Badge, Button, Card, Field, inputClass } from "@/components/ui";

export type CrewMember = {
  id: string; first_name: string; last_name: string; position: string | null; email: string | null;
  phone: string | null; nationality: string | null; status: "active" | "inactive";
  start_date: string | null; end_date: string | null;
};
type Envelope<T> = { data: T };
type Draft = Omit<CrewMember, "id">;
const blank: Draft = { first_name: "", last_name: "", position: "", email: "", phone: "", nationality: "", status: "active", start_date: "", end_date: "" };

export function CrewFeature({ screen }: { screen: ScreenDefinition }) {
  const { activeYacht, request } = useAuth();
  const router = useRouter();
  const [members, setMembers] = useState<CrewMember[]>([]);
  const [member, setMember] = useState<CrewMember | null>(null);
  const [draft, setDraft] = useState<Draft>(blank);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const load = useCallback(async () => {
    setLoading(true); setError("");
    try {
      const result = await request<Envelope<CrewMember[]>>("/crew");
      setMembers(result.data);
      if (screen.path !== "/crew" && screen.path !== "/crew/new") {
        const id = screen.path.split("/")[2];
        if (id && id !== "cem-arslan") {
          const detail = await request<Envelope<CrewMember>>(`/crew/${encodeURIComponent(id)}`);
          setMember(detail.data);
        } else if (result.data.length) {
          setMember(result.data[0]);
        } else {
          setMember(null);
        }
      }
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Mürettebat bilgileri yüklenemedi.");
    } finally { setLoading(false); }
  }, [request, screen.path]);

  useEffect(() => { setMember(null); void load(); }, [load, activeYacht?.id]);

  const isNew = screen.path === "/crew/new";
  const editing = screen.path.endsWith("/edit");
  const formMember = editing ? member : null;
  useEffect(() => {
    if (isNew) setDraft(blank);
    else if (formMember) setDraft(toDraft(formMember));
  }, [isNew, formMember]);

  async function save(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault(); setSaving(true); setError(""); setFieldErrors({});
    const payload = cleanDraft(draft);
    try {
      if (editing && member) {
        await request<Envelope<CrewMember>>(`/crew/${member.id}`, { method: "PATCH", body: payload });
        router.push(`/crew/${member.id}`);
      } else {
        const created = await request<Envelope<CrewMember>>("/crew", { method: "POST", body: payload });
        router.push(`/crew/${created.data.id}`);
      }
    } catch (cause) {
      if (cause instanceof ApiError) setFieldErrors(cause.errors);
      setError(cause instanceof Error ? cause.message : "Değişiklik kaydedilemedi.");
    } finally { setSaving(false); }
  }

  function field(name: keyof Draft, value: string) { setDraft(current => ({ ...current, [name]: value })); }
  const heading = screen.path === "/crew" ? "Mürettebat" : isNew ? "Yeni Mürettebat Üyesi" : editing ? "Mürettebat Bilgilerini Düzenle" : member ? `${member.first_name} ${member.last_name}` : "Mürettebat Üyesi";

  return <div className="animate-rise">
    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><div className="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[.12em] text-[var(--sea)]">Mürettebat <Icons.ChevronRight className="size-3" />{activeYacht?.name}</div><h1 className="text-2xl font-bold tracking-[-.035em] text-[var(--ink)] md:text-[30px]">{heading}</h1><p className="mt-1.5 text-sm text-[var(--muted)]">Aktif yatta görev yapan kişileri görüntüleyin ve yönetin.</p></div>{screen.path === "/crew" && <Link href="/crew/new"><Button><Icons.UserPlus className="size-4" />Yeni personel</Button></Link>}</div>
    {error && <div role="alert" className="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error} <button className="ml-2 font-semibold underline" onClick={() => void load()}>Tekrar dene</button></div>}
    {loading ? <Card className="grid min-h-48 place-items-center p-8"><p role="status" className="flex items-center gap-2 text-sm text-[var(--muted)]"><Icons.LoaderCircle className="size-4 animate-spin" />Mürettebat yükleniyor…</p></Card>
      : error ? null
      : screen.path === "/crew" ? <Roster members={members} />
      : isNew || editing ? <Card className="mx-auto max-w-4xl p-5 md:p-7"><form className="grid gap-5 sm:grid-cols-2" onSubmit={save}>
        <InputField name="first_name" label="Ad *" value={draft.first_name} update={field} errors={fieldErrors} />
        <InputField name="last_name" label="Soyad *" value={draft.last_name} update={field} errors={fieldErrors} />
        <InputField name="position" label="Görev / Pozisyon" value={draft.position ?? ""} update={field} errors={fieldErrors} />
        <InputField name="email" label="E-posta" type="email" value={draft.email ?? ""} update={field} errors={fieldErrors} />
        <InputField name="phone" label="Telefon" value={draft.phone ?? ""} update={field} errors={fieldErrors} />
        <InputField name="nationality" label="Uyruk (2 harf)" maxLength={2} value={draft.nationality ?? ""} update={field} errors={fieldErrors} />
        <InputField name="start_date" label="Başlangıç tarihi" type="date" value={draft.start_date ?? ""} update={field} errors={fieldErrors} />
        <InputField name="end_date" label="Bitiş tarihi" type="date" value={draft.end_date ?? ""} update={field} errors={fieldErrors} />
        <Field label="Durum"><select className={inputClass} value={draft.status} onChange={e => field("status", e.target.value)}><option value="active">Aktif</option><option value="inactive">Pasif</option></select></Field>
        <div className="flex items-end text-xs text-[var(--muted)]">Yat ve organizasyon bilgileri güvenli oturumunuzdan alınır.</div>
        <div className="flex justify-end gap-2 border-t border-[var(--line)] pt-5 sm:col-span-2"><Link href={editing && member ? `/crew/${member.id}` : "/crew"}><Button variant="secondary" type="button">Vazgeç</Button></Link><Button type="submit" disabled={saving}><Icons.Check className="size-4" />{saving ? "Kaydediliyor…" : "Kaydet"}</Button></div>
      </form></Card>
      : member ? <MemberDetail member={member} />
      : <Empty />}
  </div>;
}

function Roster({ members }: { members: CrewMember[] }) {
  const [query, setQuery] = useState("");
  const rows = members.filter(x => `${x.first_name} ${x.last_name} ${x.position ?? ""}`.toLocaleLowerCase().includes(query.toLocaleLowerCase()));
  if (!members.length) return <Empty />;
  return <Card className="overflow-hidden"><div className="border-b border-[var(--line)] p-4"><div className="relative max-w-md"><Icons.Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]" /><input aria-label="Mürettebat ara" className={`${inputClass} pl-9`} value={query} onChange={e => setQuery(e.target.value)} placeholder="İsim veya görev ara…" /></div></div><div className="overflow-x-auto"><table className="w-full min-w-[680px] text-left text-sm"><thead className="bg-[var(--surface-muted)] text-xs uppercase tracking-wide text-[var(--muted)]"><tr>{["Mürettebat Üyesi", "Pozisyon", "İletişim", "Başlangıç", "Durum", ""].map(x => <th className="px-5 py-3 font-semibold" key={x}>{x}</th>)}</tr></thead><tbody>{rows.map(row => <tr className="border-t border-[var(--line)] hover:bg-[var(--surface-muted)]" key={row.id}><td className="px-5 py-4"><Link href={`/crew/${row.id}`} className="flex items-center gap-3"><Avatar initials={initials(row)} /><span className="font-semibold">{row.first_name} {row.last_name}</span></Link></td><td className="px-5 py-4">{row.position || "—"}</td><td className="px-5 py-4 text-[var(--muted)]">{row.email || row.phone || "—"}</td><td className="px-5 py-4 text-[var(--muted)]">{row.start_date || "—"}</td><td className="px-5 py-4"><Badge tone={row.status === "active" ? "success" : "default"}>{row.status === "active" ? "Aktif" : "Pasif"}</Badge></td><td className="px-4"><Link aria-label={`${row.first_name} ${row.last_name} görüntüle`} href={`/crew/${row.id}`}><Icons.ChevronRight className="size-4" /></Link></td></tr>)}</tbody></table>{!rows.length && <p className="p-8 text-center text-sm text-[var(--muted)]">Aramanızla eşleşen mürettebat yok.</p>}</div><div className="border-t border-[var(--line)] p-4 text-xs text-[var(--muted)]">{members.length} mürettebat üyesi</div></Card>;
}

function MemberDetail({ member }: { member: CrewMember }) {
  return <div className="grid gap-5 lg:grid-cols-[1fr_340px]"><Card className="p-6"><div className="flex items-center gap-4"><Avatar initials={initials(member)} className="size-14 text-base" /><div className="flex-1"><h2 className="text-xl font-bold">{member.first_name} {member.last_name}</h2><p className="text-sm text-[var(--muted)]">{member.position || "Pozisyon belirtilmedi"}</p></div><Badge tone={member.status === "active" ? "success" : "default"}>{member.status === "active" ? "Aktif" : "Pasif"}</Badge><Link href={`/crew/${member.id}/edit`}><Button variant="secondary"><Icons.Pencil className="size-4" />Düzenle</Button></Link></div><div className="mt-7 grid gap-5 border-t border-[var(--line)] pt-6 sm:grid-cols-2">{[["E-posta", member.email], ["Telefon", member.phone], ["Uyruk", member.nationality], ["Başlangıç", member.start_date], ["Bitiş", member.end_date]].map(([label, value]) => <div key={label}><div className="text-xs font-medium uppercase text-[var(--muted)]">{label}</div><div className="mt-1 text-sm font-semibold">{value || "—"}</div></div>)}</div></Card><Card className="h-fit p-5"><h3 className="font-bold">Görev kaydı</h3><p className="mt-2 text-sm leading-6 text-[var(--muted)]">Bu kayıt aktif yatın operasyonel mürettebat listesindedir. Servis geçmişi korunur; ayrılan personeli pasif olarak işaretleyin.</p></Card></div>;
}

function InputField({ name, label, type = "text", value, update, errors, maxLength }: { name: keyof Draft; label: string; type?: string; value: string; update: (name: keyof Draft, value: string) => void; errors: Record<string, string[]>; maxLength?: number }) {
  return <Field label={label}><input className={inputClass} type={type} maxLength={maxLength} value={value} onChange={e => update(name, name === "nationality" ? e.target.value.toUpperCase() : e.target.value)} />{errors[name] && <span className="text-xs font-medium text-red-600">{errors[name][0]}</span>}</Field>;
}

function Empty() { return <Card className="grid min-h-64 place-items-center p-8 text-center"><div><span className="mx-auto grid size-12 place-items-center rounded-2xl bg-[var(--sea-soft)] text-[var(--sea)]"><Icons.Users className="size-6" /></span><h2 className="mt-4 font-bold">Henüz mürettebat yok</h2><p className="mt-1 text-sm text-[var(--muted)]">Aktif yat için ilk mürettebat üyesini ekleyin.</p><Link href="/crew/new" className="mt-4 inline-block"><Button><Icons.UserPlus className="size-4" />Mürettebat ekle</Button></Link></div></Card>; }
function initials(member: Pick<CrewMember, "first_name" | "last_name">) { return `${member.first_name[0] ?? ""}${member.last_name[0] ?? ""}`.toUpperCase(); }
function toDraft(member: CrewMember): Draft { const { id: _id, ...rest } = member; return { ...rest, position: rest.position ?? "", email: rest.email ?? "", phone: rest.phone ?? "", nationality: rest.nationality ?? "", start_date: rest.start_date ?? "", end_date: rest.end_date ?? "" }; }
function cleanDraft(draft: Draft) { return Object.fromEntries(Object.entries(draft).map(([key, value]) => [key, typeof value === "string" && value === "" ? null : value])); }
