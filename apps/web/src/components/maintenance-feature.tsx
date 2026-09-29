"use client";

import Link from "next/link";
import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import * as Icons from "lucide-react";
import { useAuth } from "@/lib/auth/auth-provider";
import { ApiError } from "@/lib/api/client";
import type { ScreenDefinition } from "@/data/screens";
import { Avatar, Badge, Button, Card, Field, inputClass } from "@/components/ui";

export type MaintenanceTask = {
  id: string; title: string; description: string | null; type: "preventive" | "corrective" | "inspection";
  status: "planned" | "in_progress" | "completed" | "cancelled"; priority: "low" | "normal" | "high" | "critical";
  due_date: string; completed_at: string | null; is_overdue: boolean;
  assignees: Array<{ id: string; first_name: string; last_name: string; position: string | null }>;
};
type Crew = { id: string; first_name: string; last_name: string; position: string | null; status: string };
type Draft = { title: string; description: string; type: MaintenanceTask["type"]; status: Exclude<MaintenanceTask["status"], "completed">; priority: MaintenanceTask["priority"]; due_date: string; assignee_ids: string[] };
type Envelope<T> = { data: T };
const today = () => new Date().toISOString().slice(0, 10);
const emptyDraft = (): Draft => ({ title: "", description: "", type: "preventive", status: "planned", priority: "normal", due_date: today(), assignee_ids: [] });

export function MaintenanceFeature({ screen }: { screen: ScreenDefinition }) {
  const { activeYacht, request } = useAuth();
  const router = useRouter();
  const [tasks, setTasks] = useState<MaintenanceTask[]>([]);
  const [crew, setCrew] = useState<Crew[]>([]);
  const [task, setTask] = useState<MaintenanceTask | null>(null);
  const [draft, setDraft] = useState<Draft>(emptyDraft);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [completing, setCompleting] = useState(false);
  const [error, setError] = useState("");
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const load = useCallback(async () => {
    setLoading(true); setError(""); setTask(null);
    try {
      const [result, people] = await Promise.all([
        request<Envelope<MaintenanceTask[]>>("/maintenance"),
        request<Envelope<Crew[]>>("/crew"),
      ]);
      setTasks(result.data);
      setCrew(people.data.filter(person => person.status === "active"));
      if (!isLandingOrList(screen.path) && screen.path !== "/maintenance/work-orders/new") {
        const segment = screen.path.split("/")[3];
        if (segment && segment !== "edit") {
          const detail = await request<Envelope<MaintenanceTask>>(`/maintenance/${encodeURIComponent(segment)}`);
          setTask(detail.data);
        }
      }
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Bakım kayıtları yüklenemedi.");
    } finally { setLoading(false); }
  }, [request, screen.path]);

  useEffect(() => { setTask(null); void load(); }, [load, activeYacht?.id]);

  const isNew = screen.path === "/maintenance/work-orders/new";
  const isEdit = screen.path.endsWith("/edit");
  useEffect(() => {
    if (isNew) setDraft(emptyDraft());
    else if (isEdit && task) setDraft(taskToDraft(task));
  }, [isNew, isEdit, task]);

  const isList = screen.path === "/maintenance/work-orders";
  const isHome = screen.path === "/maintenance";
  const heading = isHome ? "Bakım Merkezi" : isList ? "İş Emirleri" : isNew ? "Yeni Bakım İşi" : isEdit ? "Bakım İşini Düzenle" : task?.title ?? "Bakım İşi";
  const overdueCount = tasks.filter(item => item.is_overdue).length;
  const openCount = tasks.filter(item => ["planned", "in_progress"].includes(item.status)).length;
  const dueSoonCount = tasks.filter(item => {
    const days = dayOffset(item.due_date);
    return ["planned", "in_progress"].includes(item.status) && days >= 0 && days <= 7;
  }).length;

  async function save(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault(); setSaving(true); setError(""); setFieldErrors({});
    try {
      if (isEdit && task) {
        await request<Envelope<MaintenanceTask>>(`/maintenance/${task.id}`, { method: "PATCH", body: draft });
        router.push(`/maintenance/work-orders/${task.id}`);
      } else {
        const created = await request<Envelope<MaintenanceTask>>("/maintenance", { method: "POST", body: {
          ...draft, status: undefined,
        } });
        router.push(`/maintenance/work-orders/${created.data.id}`);
      }
    } catch (cause) {
      if (cause instanceof ApiError) setFieldErrors(cause.errors);
      setError(cause instanceof Error ? cause.message : "Bakım işi kaydedilemedi.");
    } finally { setSaving(false); }
  }

  async function changeStatus(status: "in_progress" | "cancelled") {
    if (!task) return;
    setError("");
    try {
      const changed = await request<Envelope<MaintenanceTask>>(`/maintenance/${task.id}`, { method: "PATCH", body: { status } });
      setTask(changed.data);
      setTasks(current => current.map(item => item.id === task.id ? changed.data : item));
    } catch (cause) { setError(cause instanceof Error ? cause.message : "Durum değiştirilemedi."); }
  }

  async function complete() {
    if (!task) return;
    setCompleting(true); setError("");
    try {
      const completed = await request<Envelope<MaintenanceTask>>(`/maintenance/${task.id}/complete`, { method: "POST" });
      setTask(completed.data);
      setTasks(current => current.map(item => item.id === task.id ? completed.data : item));
    } catch (cause) { setError(cause instanceof Error ? cause.message : "Bakım işi tamamlanamadı."); }
    finally { setCompleting(false); }
  }

  function set<K extends keyof Draft>(key: K, value: Draft[K]) { setDraft(current => ({ ...current, [key]: value })); }
  const filtered = useMemo(() => tasks, [tasks]);

  return <div className="animate-rise">
    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><div className="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[.12em] text-[var(--sea)]">Bakım <Icons.ChevronRight className="size-3" />{activeYacht?.name}</div><h1 className="text-2xl font-bold tracking-[-.035em] text-[var(--ink)] md:text-[30px]">{heading}</h1><p className="mt-1.5 text-sm text-[var(--muted)]">Yat bakım işlerini planlayın, sorumluları belirleyin ve tamamlanmayı izleyin.</p></div>{(isHome || isList) && <Link href="/maintenance/work-orders/new"><Button><Icons.Plus className="size-4" />Yeni bakım işi</Button></Link>}</div>
    {error && <div role="alert" className="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error} <button className="ml-2 font-semibold underline" onClick={() => void load()}>Tekrar dene</button></div>}
    {loading ? <Card className="grid min-h-48 place-items-center p-8"><p role="status" className="flex items-center gap-2 text-sm text-[var(--muted)]"><Icons.LoaderCircle className="size-4 animate-spin" />Bakım kayıtları yükleniyor…</p></Card>
      : error ? null
      : isHome ? <MaintenanceOverview tasks={filtered} openCount={openCount} overdueCount={overdueCount} dueSoonCount={dueSoonCount} />
      : isList ? <MaintenanceList tasks={filtered} />
      : isNew || isEdit ? <MaintenanceForm draft={draft} crew={crew} errors={fieldErrors} saving={saving} editing={isEdit} onField={set} onSave={save} taskId={task?.id} />
      : task ? <MaintenanceDetail task={task} completing={completing} onComplete={complete} onStatus={changeStatus} />
      : <MaintenanceEmpty />}
  </div>;
}

function MaintenanceOverview({ tasks, openCount, overdueCount, dueSoonCount }: { tasks: MaintenanceTask[]; openCount: number; overdueCount: number; dueSoonCount: number }) {
  const upcoming = tasks.filter(item => ["planned", "in_progress"].includes(item.status)).slice(0, 5);
  return <div className="space-y-5"><div className="grid gap-4 sm:grid-cols-3"><Metric label="Açık bakım işi" value={openCount} icon="ClipboardList" /><Metric label="Gecikmiş" value={overdueCount} icon="TriangleAlert" danger /><Metric label="7 gün içinde" value={dueSoonCount} icon="CalendarClock" /></div><Card className="p-5"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Açık bakım işleri</h2><p className="text-sm text-[var(--muted)]">Termin sırasına göre</p></div><Link href="/maintenance/work-orders" className="text-sm font-semibold text-[var(--sea)]">Tüm işleri gör</Link></div>{upcoming.length ? <TaskRows tasks={upcoming} /> : <p className="py-8 text-center text-sm text-[var(--muted)]">Planlı bakım işi bulunmuyor.</p>}</Card></div>;
}

function MaintenanceList({ tasks }: { tasks: MaintenanceTask[] }) {
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState("");
  const [priority, setPriority] = useState("");
  const rows = tasks.filter(item => (!status || item.status === status) && (!priority || item.priority === priority)
    && `${item.title} ${item.description ?? ""} ${item.assignees.map(person => person.first_name + " " + person.last_name).join(" ")}`.toLowerCase().includes(query.toLowerCase()));
  if (!tasks.length) return <MaintenanceEmpty />;
  return <Card className="overflow-hidden"><div className="flex flex-col gap-3 border-b border-[var(--line)] p-4 lg:flex-row"><input aria-label="Bakım işlerinde ara" className={`${inputClass} lg:max-w-sm`} value={query} onChange={event => setQuery(event.target.value)} placeholder="İş veya sorumlu ara…" /><select aria-label="Duruma göre filtrele" className={inputClass} value={status} onChange={event => setStatus(event.target.value)}><option value="">Tüm durumlar</option><option value="planned">Planlandı</option><option value="in_progress">Devam ediyor</option><option value="completed">Tamamlandı</option><option value="cancelled">İptal edildi</option></select><select aria-label="Önceliğe göre filtrele" className={inputClass} value={priority} onChange={event => setPriority(event.target.value)}><option value="">Tüm öncelikler</option>{(["critical", "high", "normal", "low"] as MaintenanceTask["priority"][]).map(value => <option key={value} value={value}>{priorityLabel(value)}</option>)}</select></div>{rows.length ? <TaskRows tasks={rows} /> : <p className="p-8 text-center text-sm text-[var(--muted)]">Filtreyle eşleşen bakım işi bulunamadı.</p>}<div className="border-t border-[var(--line)] p-4 text-xs text-[var(--muted)]">{rows.length} / {tasks.length} bakım işi</div></Card>;
}

function TaskRows({ tasks }: { tasks: MaintenanceTask[] }) {
  return <div className="overflow-x-auto"><table className="w-full min-w-[780px] text-left text-sm"><thead className="bg-[var(--surface-muted)] text-xs uppercase tracking-wide text-[var(--muted)]"><tr>{["Bakım işi", "Öncelik", "Durum", "Termin", "Sorumlu", ""].map(label => <th key={label} className="px-5 py-3 font-semibold">{label}</th>)}</tr></thead><tbody>{tasks.map(item => <tr key={item.id} className="border-t border-[var(--line)] hover:bg-[var(--surface-muted)]"><td className="px-5 py-4"><Link href={`/maintenance/work-orders/${item.id}`} className="font-semibold hover:text-[var(--sea)]">{item.title}</Link><div className="mt-1 text-xs text-[var(--muted)]">{typeLabel(item.type)}</div></td><td className="px-5 py-4"><Badge tone={priorityTone(item.priority)}>{priorityLabel(item.priority)}</Badge></td><td className="px-5 py-4"><Badge tone={statusTone(item.status)}>{statusLabel(item.status)}</Badge></td><td className="px-5 py-4"><span className={item.is_overdue ? "font-semibold text-red-600" : "text-[var(--muted)]"}>{item.due_date}</span>{item.is_overdue && <div className="text-xs text-red-600">Gecikmiş</div>}</td><td className="px-5 py-4">{item.assignees.length ? <div className="flex flex-wrap gap-1">{item.assignees.map(person => <span className="inline-flex items-center gap-1 text-xs" key={person.id}><Avatar initials={initials(person)} className="size-6" />{person.first_name} {person.last_name}</span>)}</div> : <span className="text-[var(--muted)]">Atanmadı</span>}</td><td className="px-4"><Link aria-label={`${item.title} ayrıntıları`} href={`/maintenance/work-orders/${item.id}`}><Icons.ChevronRight className="size-4" /></Link></td></tr>)}</tbody></table></div>;
}

function MaintenanceDetail({ task, completing, onComplete, onStatus }: { task: MaintenanceTask; completing: boolean; onComplete: () => void; onStatus: (status: "in_progress" | "cancelled") => void }) {
  return <div className="grid gap-5 xl:grid-cols-[1fr_340px]"><Card className="p-6"><div className="flex flex-wrap items-start gap-3"><div className="flex-1"><h2 className="text-xl font-bold">{task.title}</h2><p className="mt-2 whitespace-pre-line text-sm leading-6 text-[var(--muted)]">{task.description || "Açıklama eklenmemiş."}</p></div><Badge tone={statusTone(task.status)}>{statusLabel(task.status)}</Badge><Badge tone={priorityTone(task.priority)}>{priorityLabel(task.priority)}</Badge></div><div className="mt-6 grid gap-5 border-t border-[var(--line)] pt-6 sm:grid-cols-2">{[["Tür", typeLabel(task.type)], ["Termin", task.due_date], ["Tamamlanma", task.completed_at ? new Date(task.completed_at).toLocaleString("tr-TR") : "—"]].map(([label, value]) => <div key={label}><div className="text-xs font-medium uppercase text-[var(--muted)]">{label}</div><div className="mt-1 text-sm font-semibold">{value}</div></div>)}<div><div className="text-xs font-medium uppercase text-[var(--muted)]">Sorumlular</div><div className="mt-2 flex flex-wrap gap-2">{task.assignees.length ? task.assignees.map(person => <span className="inline-flex items-center gap-2 rounded-xl bg-[var(--surface-muted)] px-2 py-1 text-sm" key={person.id}><Avatar initials={initials(person)} className="size-7" />{person.first_name} {person.last_name}</span>) : <span className="text-sm text-[var(--muted)]">Atanmadı</span>}</div></div></div>{task.is_overdue && <div className="mt-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">Bu bakım işi gecikmiştir.</div>}</Card><Card className="h-fit space-y-3 p-5"><h3 className="font-bold">İşlemler</h3>{!["completed", "cancelled"].includes(task.status) && <Link href={`/maintenance/work-orders/${task.id}/edit`}><Button variant="secondary" className="w-full"><Icons.Pencil className="size-4" />Düzenle</Button></Link>}{task.status === "planned" && <Button variant="secondary" className="w-full" onClick={() => onStatus("in_progress")}><Icons.Play className="size-4" />İşe başla</Button>}{["planned", "in_progress"].includes(task.status) && <Button className="w-full" disabled={completing} onClick={onComplete}><Icons.Check className="size-4" />{completing ? "Tamamlanıyor…" : "Tamamlandı olarak işaretle"}</Button>}{task.status === "planned" && <Button variant="ghost" className="w-full text-red-600" onClick={() => onStatus("cancelled")}><Icons.CircleX className="size-4" />İşi iptal et</Button>}</Card></div>;
}

function MaintenanceForm({ draft, crew, errors, saving, editing, onField, onSave, taskId }: { draft: Draft; crew: Crew[]; errors: Record<string, string[]>; saving: boolean; editing: boolean; onField: <K extends keyof Draft>(key: K, value: Draft[K]) => void; onSave: (event: React.FormEvent<HTMLFormElement>) => void; taskId?: string }) {
  const assigneeError = errors.assignee_ids?.[0] ?? errors["assignee_ids.0"]?.[0];
  return <Card className="mx-auto max-w-5xl p-5 md:p-7"><form className="grid gap-5 sm:grid-cols-2" onSubmit={onSave}><TextInput label="Bakım işi *" value={draft.title} error={errors.title?.[0]} onChange={value => onField("title", value)} /><Field label="Tür *"><select className={inputClass} value={draft.type} onChange={event => onField("type", event.target.value as Draft["type"])}><option value="preventive">Önleyici bakım</option><option value="corrective">Düzeltici bakım</option><option value="inspection">Kontrol</option></select>{errors.type?.[0] && <FieldError text={errors.type[0]} />}</Field><Field label="Öncelik"><select className={inputClass} value={draft.priority} onChange={event => onField("priority", event.target.value as Draft["priority"])}>{(["low", "normal", "high", "critical"] as MaintenanceTask["priority"][]).map(value => <option key={value} value={value}>{priorityLabel(value)}</option>)}</select></Field><Field label="Termin *"><input className={inputClass} type="date" value={draft.due_date} onChange={event => onField("due_date", event.target.value)} />{errors.due_date?.[0] && <FieldError text={errors.due_date[0]} />}</Field>{editing && <Field label="Durum"><select className={inputClass} value={draft.status} onChange={event => onField("status", event.target.value as Draft["status"])}><option value="planned">Planlandı</option><option value="in_progress">Devam ediyor</option><option value="cancelled">İptal edildi</option></select>{errors.status?.[0] && <FieldError text={errors.status[0]} />}</Field>}<Field label="Açıklama"><textarea className={`${inputClass} h-28 py-3`} value={draft.description} onChange={event => onField("description", event.target.value)} placeholder="Yapılacak işi ve gerekli kontrolleri açıklayın." />{errors.description?.[0] && <FieldError text={errors.description[0]} />}</Field><fieldset className="rounded-xl border border-[var(--line)] p-4 sm:col-span-2"><legend className="px-1 text-sm font-semibold">Sorumlular</legend>{assigneeError && <FieldError text={assigneeError} />}{crew.length ? <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">{crew.map(person => <label className="flex items-center gap-3 rounded-lg bg-[var(--surface-muted)] p-3 text-sm" key={person.id}><input type="checkbox" checked={draft.assignee_ids.includes(person.id)} onChange={event => onField("assignee_ids", event.target.checked ? [...draft.assignee_ids, person.id] : draft.assignee_ids.filter(id => id !== person.id))} /><span><b>{person.first_name} {person.last_name}</b><small className="block text-[var(--muted)]">{person.position || "Mürettebat"}</small></span></label>)}</div> : <p className="text-sm text-[var(--muted)]">Aktif mürettebat bulunamadı. Önce Mürettebat bölümünden kişi ekleyin.</p>}</fieldset><p className="text-xs text-[var(--muted)] sm:col-span-2">Yat ve organizasyon bilgileri aktif sunucu bağlamından alınır.</p><div className="flex justify-end gap-2 border-t border-[var(--line)] pt-5 sm:col-span-2"><Link href={editing && taskId ? `/maintenance/work-orders/${taskId}` : "/maintenance/work-orders"}><Button variant="secondary" type="button">Vazgeç</Button></Link><Button type="submit" disabled={saving}><Icons.Check className="size-4" />{saving ? "Kaydediliyor…" : "Kaydet"}</Button></div></form></Card>;
}

function TextInput({ label, value, error, onChange }: { label: string; value: string; error?: string; onChange: (value: string) => void }) { return <Field label={label}><input className={inputClass} maxLength={180} value={value} onChange={event => onChange(event.target.value)} />{error && <FieldError text={error} />}</Field>; }
function FieldError({ text }: { text: string }) { return <span className="text-xs font-medium text-red-600">{text}</span>; }
function MaintenanceEmpty() { return <Card className="grid min-h-64 place-items-center p-8 text-center"><div><span className="mx-auto grid size-12 place-items-center rounded-2xl bg-[var(--sea-soft)] text-[var(--sea)]"><Icons.Wrench className="size-6" /></span><h2 className="mt-4 font-bold">Bakım işi bulunamadı</h2><p className="mt-1 text-sm text-[var(--muted)]">Aktif yat için ilk bakım işini oluşturun.</p><Link href="/maintenance/work-orders/new" className="mt-4 inline-block"><Button><Icons.Plus className="size-4" />Bakım işi oluştur</Button></Link></div></Card>; }
function Metric({ label, value, icon, danger = false }: { label: string; value: number; icon: "ClipboardList" | "TriangleAlert" | "CalendarClock"; danger?: boolean }) { const Icon = icon === "ClipboardList" ? Icons.ClipboardList : icon === "TriangleAlert" ? Icons.TriangleAlert : Icons.CalendarClock; return <Card className="p-5"><div className="flex items-center justify-between"><span className="text-sm font-medium text-[var(--muted)]">{label}</span><span className={`grid size-9 place-items-center rounded-xl ${danger ? "bg-red-50 text-red-600" : "bg-[var(--surface-muted)] text-[var(--sea)]"}`}><Icon className="size-[18px]" /></span></div><div className={`mt-4 text-2xl font-bold ${danger && value ? "text-red-600" : ""}`}>{value}</div></Card>; }
function isLandingOrList(path: string) { return path === "/maintenance" || path === "/maintenance/work-orders"; }
function taskToDraft(task: MaintenanceTask): Draft { return { title: task.title, description: task.description ?? "", type: task.type, status: task.status === "completed" ? "in_progress" : task.status, priority: task.priority, due_date: task.due_date, assignee_ids: task.assignees.map(person => person.id) }; }
function dayOffset(date: string) { return Math.floor((Date.parse(`${date}T00:00:00Z`) - Date.parse(`${today()}T00:00:00Z`)) / 86400000); }
function statusLabel(status: MaintenanceTask["status"]) { return ({ planned: "Planlandı", in_progress: "Devam ediyor", completed: "Tamamlandı", cancelled: "İptal edildi" })[status]; }
function statusTone(status: MaintenanceTask["status"]): "success" | "warning" | "default" | "info" { return status === "completed" ? "success" : status === "in_progress" ? "warning" : status === "cancelled" ? "default" : "info"; }
function priorityLabel(priority: MaintenanceTask["priority"]) { return ({ low: "Düşük", normal: "Normal", high: "Yüksek", critical: "Kritik" })[priority]; }
function priorityTone(priority: MaintenanceTask["priority"]): "danger" | "warning" | "default" | "info" { return priority === "critical" ? "danger" : priority === "high" ? "warning" : priority === "normal" ? "info" : "default"; }
function typeLabel(type: MaintenanceTask["type"]) { return ({ preventive: "Önleyici bakım", corrective: "Düzeltici bakım", inspection: "Kontrol" })[type]; }
function initials(person: { first_name: string; last_name: string }) { return `${person.first_name[0] ?? ""}${person.last_name[0] ?? ""}`.toUpperCase(); }
