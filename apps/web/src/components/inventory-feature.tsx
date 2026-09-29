"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import * as Icons from "lucide-react";
import { useAuth } from "@/lib/auth/auth-provider";
import { ApiError } from "@/lib/api/client";
import type { ScreenDefinition } from "@/data/screens";
import { Badge, Button, Card, Field, inputClass } from "@/components/ui";

type Unit = "piece" | "liter" | "kilogram" | "meter" | "pack";
type Movement = { id: string; type: "in" | "out" | "adjustment"; quantity: string; balance_after: string; reason: string; note: string | null; performed_by: string; created_at: string };
type Item = { id: string; name: string; description: string | null; category: string; unit: Unit; current_quantity: string; minimum_quantity: string | null; is_low_stock: boolean; storage_location: string | null; recent_movements?: Movement[] };
type Envelope<T> = { data: T };
type Draft = { name: string; description: string; category: string; unit: Unit; minimum_quantity: string; storage_location: string };
const emptyDraft = (): Draft => ({ name: "", description: "", category: "", unit: "piece", minimum_quantity: "", storage_location: "" });

export function InventoryFeature({ screen }: { screen: ScreenDefinition }) {
  const { request, activeYacht } = useAuth();
  const router = useRouter();
  const [items, setItems] = useState<Item[]>([]);
  const [item, setItem] = useState<Item | null>(null);
  const [draft, setDraft] = useState<Draft>(emptyDraft);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const isHome = screen.path === "/inventory";
  const isList = screen.path === "/inventory/items";
  const isNew = screen.path === "/inventory/items/new";
  const isEdit = screen.path.endsWith("/edit");
  const isForm = isNew || isEdit;
  const itemId = screen.path.split("/")[3];

  const load = useCallback(async () => {
    setLoading(true); setError(""); setItem(null);
    try {
      const result = await request<Envelope<Item[]>>("/inventory");
      setItems(result.data);
      if (!isHome && !isList && !isNew) {
        const detail = await request<Envelope<Item>>(`/inventory/${encodeURIComponent(itemId)}`);
        setItem(detail.data);
      }
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Stok kayÄ±tlarÄ± yÃ¼klenemedi.");
    } finally { setLoading(false); }
  }, [request, isHome, isList, isNew, itemId]);

  useEffect(() => { setItem(null); void load(); }, [load, activeYacht?.id]);
  useEffect(() => {
    if (isNew) setDraft(emptyDraft());
    else if (isEdit && item) setDraft({ name: item.name, description: item.description ?? "", category: item.category, unit: item.unit, minimum_quantity: item.minimum_quantity ?? "", storage_location: item.storage_location ?? "" });
  }, [isNew, isEdit, item]);

  const lowCount = items.filter(value => value.is_low_stock).length;
  const totalItems = items.length;

  async function save(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault(); setSaving(true); setError(""); setErrors({});
    const payload = { ...draft, minimum_quantity: draft.minimum_quantity || null, description: draft.description || null, storage_location: draft.storage_location || null };
    try {
      if (isEdit && item) {
        await request<Envelope<Item>>(`/inventory/${item.id}`, { method: "PATCH", body: payload });
        router.push(`/inventory/items/${item.id}`);
      } else {
        const created = await request<Envelope<Item>>("/inventory", { method: "POST", body: payload });
        router.push(`/inventory/items/${created.data.id}`);
      }
    } catch (cause) {
      if (cause instanceof ApiError) setErrors(cause.errors);
      setError(cause instanceof Error ? cause.message : "Stok Ã¼rÃ¼nÃ¼ kaydedilemedi.");
    } finally { setSaving(false); }
  }

  async function move(type: Movement["type"], quantity: string, reason: string, note: string) {
    if (!item) return;
    setError(""); setErrors({});
    try {
      await request<Envelope<Movement>>(`/inventory/${item.id}/movements`, { method: "POST", body: { type, quantity, reason, note: note || null } });
      await load();
    } catch (cause) {
      if (cause instanceof ApiError) setErrors(cause.errors);
      setError(cause instanceof Error ? cause.message : "Stok hareketi kaydedilemedi.");
    }
  }

  return <main className="animate-rise">
    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><div className="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[.12em] text-[var(--sea)]">Stok <Icons.ChevronRight className="size-3" />{activeYacht?.name}</div><h1 className="text-2xl font-bold tracking-[-.035em] text-[var(--ink)] md:text-[30px]">{isHome ? "Stok Merkezi" : isList ? "Stok Ürünleri" : isNew ? "Yeni Stok Ürünü" : isEdit ? "Ürünü Düzenle" : item?.name ?? "Stok Ürünü"}</h1><p className="mt-1.5 text-sm text-[var(--muted)]">Yat stoklarını, minimum seviyeleri ve hareket geçmişini yönetin.</p></div>{(isHome || isList) && <Link href="/inventory/items/new"><Button><Icons.Plus className="size-4" />Ürün ekle</Button></Link>}</div>
    {error && <div role="alert" className="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error} <button className="ml-2 font-semibold underline" onClick={() => void load()}>Tekrar dene</button></div>}
    {loading ? <Card className="grid min-h-48 place-items-center p-8"><p role="status" className="flex items-center gap-2 text-sm text-[var(--muted)]"><Icons.LoaderCircle className="size-4 animate-spin" />Stok kayıtları yükleniyor…</p></Card>
      : error ? null
      : isHome ? <Overview items={items} lowCount={lowCount} />
      : isList ? <ItemList items={items} />
      : isForm ? <ItemForm draft={draft} errors={errors} saving={saving} onChange={(key, value) => setDraft(current => ({ ...current, [key]: value }))} onSave={save} itemId={item?.id} editing={isEdit} />
      : item ? <ItemDetail item={item} errors={errors} onMove={move} />
      : <EmptyState />}
    <span className="sr-only">{totalItems} inventory items</span>
  </main>;
}

function Overview({ items, lowCount }: { items: Item[]; lowCount: number }) {
  const zeroCount = items.filter(item => quantityMilli(item.current_quantity) === 0).length;
  const urgent = items.filter(item => item.is_low_stock).slice(0, 5);
  return <div className="space-y-5"><div className="grid gap-4 sm:grid-cols-3"><Metric label="Ürün çeşidi" value={items.length} icon="Boxes" /><Metric label="Minimum seviyede" value={lowCount} icon="TriangleAlert" danger={lowCount > 0} /><Metric label="Stokta olmayan" value={zeroCount} icon="PackageCheck" /></div><Card className="p-5"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Minimum stok uyarıları</h2><p className="text-sm text-[var(--muted)]">Mevcut miktar minimuma eşit veya altında</p></div><Link href="/inventory/items" className="text-sm font-semibold text-[var(--sea)]">Tüm ürünler</Link></div>{urgent.length ? <ItemRows items={urgent} /> : <p className="py-8 text-center text-sm text-[var(--muted)]">Minimum stok altında ürün yok.</p>}</Card></div>;
}

function ItemList({ items }: { items: Item[] }) {
  const [search, setSearch] = useState("");
  const [lowOnly, setLowOnly] = useState(false);
  const rows = items.filter(item => (!lowOnly || item.is_low_stock) && `${item.name} ${item.category} ${item.storage_location ?? ""}`.toLowerCase().includes(search.toLowerCase()));
  if (!items.length) return <EmptyState />;
  return <Card className="overflow-hidden"><div className="flex flex-col gap-3 border-b border-[var(--line)] p-4 sm:flex-row"><input aria-label="Stok ürünlerinde ara" className={`${inputClass} sm:max-w-sm`} value={search} onChange={event => setSearch(event.target.value)} placeholder="Ürün, kategori veya konum ara…" /><label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={lowOnly} onChange={event => setLowOnly(event.target.checked)} />Minimum stoktakiler</label></div>{rows.length ? <ItemRows items={rows} /> : <p className="p-8 text-center text-sm text-[var(--muted)]">Aramayla eşleşen stok ürünü bulunamadı.</p>}<div className="border-t border-[var(--line)] p-4 text-xs text-[var(--muted)]">{rows.length} / {items.length} ürün</div></Card>;
}

function ItemRows({ items }: { items: Item[] }) {
  return <div className="overflow-x-auto"><table className="w-full min-w-[700px] text-left text-sm"><thead className="bg-[var(--surface-muted)] text-xs uppercase tracking-wide text-[var(--muted)]"><tr>{["Ürün", "Miktar", "Minimum", "Kategori", "Depolama konumu", ""].map(label => <th key={label} className="px-5 py-3 font-semibold">{label}</th>)}</tr></thead><tbody>{items.map(item => <tr key={item.id} className="border-t border-[var(--line)] hover:bg-[var(--surface-muted)]"><td className="px-5 py-4"><Link href={`/inventory/items/${item.id}`} className="font-semibold hover:text-[var(--sea)]">{item.name}</Link><div className="mt-1 text-xs text-[var(--muted)]">{item.unit}</div></td><td className="px-5 py-4"><span className={item.is_low_stock ? "font-semibold text-red-600" : "font-medium"}>{item.current_quantity} {unitLabel(item.unit)}</span>{item.is_low_stock && <div><Badge tone="danger">Minimum stok</Badge></div>}</td><td className="px-5 py-4 text-[var(--muted)]">{item.minimum_quantity ?? "—"}</td><td className="px-5 py-4">{item.category}</td><td className="px-5 py-4 text-[var(--muted)]">{item.storage_location || "—"}</td><td className="px-4"><Link aria-label={`${item.name} ayrıntıları`} href={`/inventory/items/${item.id}`}><Icons.ChevronRight className="size-4" /></Link></td></tr>)}</tbody></table></div>;
}

function ItemDetail({ item, errors, onMove }: { item: Item; errors: Record<string, string[]>; onMove: (type: Movement["type"], quantity: string, reason: string, note: string) => Promise<void> }) {
  const [type, setType] = useState<Movement["type"]>("in");
  const [quantity, setQuantity] = useState("");
  const [reason, setReason] = useState("");
  const [note, setNote] = useState("");
  const [moving, setMoving] = useState(false);
  async function submit(event: React.FormEvent<HTMLFormElement>) { event.preventDefault(); setMoving(true); try { await onMove(type, quantity, reason, note); setQuantity(""); setReason(""); setNote(""); } finally { setMoving(false); } }
  return <div className="grid gap-5 xl:grid-cols-[1fr_360px]"><div className="space-y-5"><Card className="p-6"><div className="flex flex-wrap items-start justify-between gap-3"><div><h2 className="text-xl font-bold">{item.name}</h2><p className="mt-2 whitespace-pre-line text-sm leading-6 text-[var(--muted)]">{item.description || "Açıklama eklenmemiş."}</p></div>{item.is_low_stock && <Badge tone="danger">Minimum stok</Badge>}</div><div className="mt-6 grid gap-5 border-t border-[var(--line)] pt-6 sm:grid-cols-2 lg:grid-cols-3">{[["Mevcut miktar", `${item.current_quantity} ${unitLabel(item.unit)}`], ["Minimum miktar", item.minimum_quantity ?? "Belirlenmedi"], ["Kategori", item.category], ["Depolama konumu", item.storage_location || "Belirtilmedi"], ["Birim", unitLabel(item.unit)]].map(([label, value]) => <div key={label}><div className="text-xs font-medium uppercase text-[var(--muted)]">{label}</div><div className="mt-1 text-sm font-semibold">{value}</div></div>)}</div></Card><Card className="overflow-hidden"><div className="border-b border-[var(--line)] p-5"><h2 className="font-bold">Son stok hareketleri</h2><p className="text-sm text-[var(--muted)]">Her hareket değişimi ve işlem sonrasındaki bakiyeyi kaydeder.</p></div>{item.recent_movements?.length ? <div className="divide-y divide-[var(--line)]">{item.recent_movements.map(movement => <div key={movement.id} className="flex flex-col gap-2 p-4 sm:flex-row sm:items-center"><div className="flex flex-1 items-center gap-3"><Badge tone={movement.type === "in" ? "success" : movement.type === "out" ? "warning" : "info"}>{movementLabel(movement.type)}</Badge><div><p className="text-sm font-semibold">{movement.reason}</p><p className="text-xs text-[var(--muted)]">{movement.note || "Not eklenmemiş."}</p><p className="text-xs text-[var(--muted)]">İşlemi yapan: {movement.performed_by}</p></div></div><div className="text-right text-sm"><b>{movement.quantity}</b><div className="text-xs text-[var(--muted)]">Bakiye: {movement.balance_after} · {new Date(movement.created_at).toLocaleString("tr-TR")}</div></div></div>)}</div> : <p className="p-8 text-center text-sm text-[var(--muted)]">Henüz stok hareketi yok.</p>}</Card></div><div className="space-y-4"><Card className="p-5"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Stok hareketi kaydet</h2><p className="text-xs text-[var(--muted)]">Düzeltme miktarı signed farktır.</p></div><Link href={`/inventory/items/${item.id}/edit`}><Button variant="secondary"><Icons.Pencil className="size-4" />Düzenle</Button></Link></div><form className="space-y-4" onSubmit={submit}><Field label="Hareket türü"><select className={inputClass} value={type} onChange={event => setType(event.target.value as Movement["type"])}><option value="in">Stok girişi (+)</option><option value="out">Stok çıkışı (miktar gir)</option><option value="adjustment">Düzeltme (signed fark)</option></select></Field><Field label={type === "adjustment" ? "Miktar farkı *" : "Miktar *"}><input className={inputClass} type="number" step="0.001" min={type === "adjustment" ? undefined : "0.001"} value={quantity} onChange={event => setQuantity(event.target.value)} />{errors.quantity?.[0] && <span className="text-xs text-red-600">{errors.quantity[0]}</span>}</Field><Field label="Neden *"><input className={inputClass} maxLength={180} value={reason} onChange={event => setReason(event.target.value)} />{errors.reason?.[0] && <span className="text-xs text-red-600">{errors.reason[0]}</span>}</Field><Field label="Not"><textarea className={`${inputClass} h-20 py-3`} value={note} onChange={event => setNote(event.target.value)} /></Field><p className="text-xs text-[var(--muted)]">Mevcut bakiye: {item.current_quantity} {unitLabel(item.unit)}. Negatif stok oluşamaz.</p><Button className="w-full" disabled={moving}><Icons.ArrowLeftRight className="size-4" />{moving ? "Kaydediliyor…" : "Hareketi kaydet"}</Button></form></Card></div></div>;
}

function ItemForm({ draft, errors, saving, onChange, onSave, itemId, editing }: { draft: Draft; errors: Record<string, string[]>; saving: boolean; onChange: <K extends keyof Draft>(key: K, value: Draft[K]) => void; onSave: (event: React.FormEvent<HTMLFormElement>) => void; itemId?: string; editing: boolean }) {
  return <Card className="mx-auto max-w-4xl p-5 md:p-7"><form className="grid gap-5 sm:grid-cols-2" onSubmit={onSave}><TextField label="Ürün adı *" value={draft.name} error={errors.name?.[0]} onChange={value => onChange("name", value)} /><TextField label="Kategori *" value={draft.category} error={errors.category?.[0]} onChange={value => onChange("category", value)} /><Field label="Birim *"><select className={inputClass} value={draft.unit} onChange={event => onChange("unit", event.target.value as Unit)}>{(["piece", "liter", "kilogram", "meter", "pack"] as Unit[]).map(unit => <option value={unit} key={unit}>{unitLabel(unit)}</option>)}</select>{errors.unit?.[0] && <FieldError text={errors.unit[0]} />}</Field><Field label="Minimum stok miktarı"><input className={inputClass} type="number" min="0" step="0.001" value={draft.minimum_quantity} onChange={event => onChange("minimum_quantity", event.target.value)} />{errors.minimum_quantity?.[0] && <FieldError text={errors.minimum_quantity[0]} />}</Field><Field label="Depolama konumu"><input className={inputClass} maxLength={120} value={draft.storage_location} onChange={event => onChange("storage_location", event.target.value)} /></Field><Field label="Açıklama"><textarea className={`${inputClass} h-28 py-3`} value={draft.description} onChange={event => onChange("description", event.target.value)} /></Field><p className="text-xs text-[var(--muted)] sm:col-span-2">{editing ? "Miktar değişikliği yalnızca stok hareketi kaydederek yapılabilir." : "Yeni ürün 0 stokla açılır. İlk miktarı eklemek için stok girişi kaydedin."}</p><div className="flex justify-end gap-2 border-t border-[var(--line)] pt-5 sm:col-span-2"><Link href={editing && itemId ? `/inventory/items/${itemId}` : "/inventory/items"}><Button variant="secondary" type="button">Vazgeç</Button></Link><Button type="submit" disabled={saving}><Icons.Check className="size-4" />{saving ? "Kaydediliyor…" : "Kaydet"}</Button></div></form></Card>;
}

function TextField({ label, value, error, onChange }: { label: string; value: string; error?: string; onChange: (value: string) => void }) { return <Field label={label}><input className={inputClass} maxLength={180} value={value} onChange={event => onChange(event.target.value)} />{error && <FieldError text={error} />}</Field>; }
function FieldError({ text }: { text: string }) { return <span className="text-xs font-medium text-red-600">{text}</span>; }
function EmptyState() { return <Card className="grid min-h-64 place-items-center p-8 text-center"><div><span className="mx-auto grid size-12 place-items-center rounded-2xl bg-[var(--sea-soft)] text-[var(--sea)]"><Icons.Boxes className="size-6" /></span><h2 className="mt-4 font-bold">Stok ürünü bulunamadı</h2><p className="mt-1 text-sm text-[var(--muted)]">Aktif yat için ilk stok kaydını oluşturun.</p><Link href="/inventory/items/new" className="mt-4 inline-block"><Button><Icons.Plus className="size-4" />Ürün ekle</Button></Link></div></Card>; }
function Metric({ label, value, icon, danger = false }: { label: string; value: string | number; icon: "Boxes" | "TriangleAlert" | "PackageCheck"; danger?: boolean }) { const Icon = icon === "Boxes" ? Icons.Boxes : icon === "TriangleAlert" ? Icons.TriangleAlert : Icons.PackageCheck; return <Card className="p-5"><div className="flex items-center justify-between"><span className="text-sm font-medium text-[var(--muted)]">{label}</span><span className={`grid size-9 place-items-center rounded-xl ${danger ? "bg-red-50 text-red-600" : "bg-[var(--surface-muted)] text-[var(--sea)]"}`}><Icon className="size-[18px]" /></span></div><div className={`mt-4 text-2xl font-bold ${danger && value ? "text-red-600" : ""}`}>{value}</div></Card>; }
function unitLabel(unit: Unit) { return ({ piece: "adet", liter: "litre", kilogram: "kg", meter: "metre", pack: "paket" })[unit]; }
function movementLabel(type: Movement["type"]) { return ({ in: "Giriş", out: "Çıkış", adjustment: "Düzeltme" })[type]; }
function quantityMilli(value: string) { const [whole, fraction = ""] = value.split("."); return Number(whole) * 1000 + Number(fraction.padEnd(3, "0")); }
