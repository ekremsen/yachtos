import { notFound, redirect } from "next/navigation";
import { AppShell } from "@/components/app-shell";
import { ScreenRenderer } from "@/components/screen-renderer";
import { screenByPath } from "@/data/screens";

export default async function Page({ params }: { params: Promise<{ slug?: string[] }> }) {
  const { slug } = await params;
  if (!slug?.length) redirect("/dashboard");
  const path = `/${slug.join("/")}`;
  const screen = screenByPath.get(path);
  if (!screen) notFound();
  return <AppShell><ScreenRenderer screen={screen} /></AppShell>;
}
