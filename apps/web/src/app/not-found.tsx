import Link from "next/link";
import { Anchor } from "lucide-react";
import { Button } from "@/components/ui";

export default function NotFound() {
  return <div className="grid min-h-screen place-items-center bg-[var(--background)] p-6 text-center"><div><div className="mx-auto grid size-16 place-items-center rounded-2xl bg-[var(--sea-soft)] text-[var(--sea)]"><Anchor className="size-8" /></div><h1 className="mt-5 text-3xl font-bold">Bu rota henüz haritada yok</h1><p className="mt-2 text-[var(--muted)]">Aradığınız YachtOS ekranı bulunamadı.</p><Link className="mt-6 inline-block" href="/dashboard"><Button>Genel bakışa dön</Button></Link></div></div>;
}
