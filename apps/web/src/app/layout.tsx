import type { Metadata } from "next";
import "./globals.css";
import { AuthProvider } from "@/lib/auth/auth-provider";

export const metadata: Metadata = {
  title: "YachtOS — Yacht Operations",
  description: "The operating system for modern yacht management.",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="tr">
      <body><AuthProvider>{children}</AuthProvider></body>
    </html>
  );
}
