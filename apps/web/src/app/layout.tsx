import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "YachtOS — Yacht Operations",
  description: "The operating system for modern yacht management.",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="tr">
      <body>{children}</body>
    </html>
  );
}
