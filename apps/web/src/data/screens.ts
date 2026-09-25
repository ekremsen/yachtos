export type ScreenKind = "dashboard" | "list" | "detail" | "form" | "calendar" | "kanban" | "report" | "settings" | "auth" | "onboarding";

export type ScreenDefinition = {
  path: string;
  title: string;
  description: string;
  kind: ScreenKind;
  module: string;
};

const define = (module: string, rows: Array<[string, string, ScreenKind, string?]>): ScreenDefinition[] =>
  rows.map(([path, title, kind, description]) => ({
    path,
    title,
    kind,
    module,
    description: description || `${title} kayıtlarını tek noktadan yönetin.`,
  }));

export const screens: ScreenDefinition[] = [
  ...define("Hesap", [
    ["/login", "YachtOS'a giriş", "auth"], ["/forgot-password", "Şifremi unuttum", "auth"],
    ["/reset-password", "Yeni şifre oluştur", "auth"], ["/invitation/demo", "Ekip davetini kabul et", "auth"],
    ["/onboarding/organization", "Organizasyonunu oluştur", "onboarding"], ["/onboarding/yacht", "İlk yatını ekle", "onboarding"],
    ["/onboarding/team", "Ekibini davet et", "onboarding"], ["/onboarding/complete", "Kurulum tamamlandı", "onboarding"],
  ]),
  ...define("Genel Bakış", [
    ["/dashboard", "Genel Bakış", "dashboard", "M/Y Azure için bugünün operasyon özeti."],
    ["/dashboard/operations", "Operasyon Merkezi", "dashboard"], ["/dashboard/calendar", "Operasyon Takvimi", "calendar"],
    ["/notifications", "Bildirim Merkezi", "list"], ["/search", "Global Arama", "list"],
  ]),
  ...define("Filom", [
    ["/yachts", "Yatlar", "list"], ["/yachts/new", "Yeni Yat", "form"], ["/yachts/azure", "M/Y Azure", "detail"],
    ["/yachts/azure/edit", "Yat Bilgilerini Düzenle", "form"], ["/yachts/azure/specifications", "Teknik Özellikler", "detail"],
    ["/yachts/azure/equipment", "Makine ve Ekipmanlar", "list"], ["/yachts/azure/documents", "Yat Belgeleri", "list"],
    ["/yachts/azure/activity", "Yat Aktivite Geçmişi", "list"],
  ]),
  ...define("Mürettebat", [
    ["/crew", "Mürettebat", "list"], ["/crew/cem-arslan", "Cem Arslan", "detail"], ["/crew/new", "Yeni Personel", "form"],
    ["/crew/cem-arslan/edit", "Personel Bilgilerini Düzenle", "form"], ["/crew/schedule", "Çalışma Programı", "calendar"],
    ["/crew/leaves", "İzin Yönetimi", "list"], ["/crew/documents", "Personel Belgeleri", "list"], ["/crew/payroll", "Maaş ve Ödemeler", "list"],
  ]),
  ...define("Görevler", [
    ["/tasks", "Görev Panosu", "kanban"], ["/tasks/list", "Görev Listesi", "list"], ["/tasks/engine-check", "Makine Kontrolü", "detail"],
    ["/tasks/calendar", "Görev Takvimi", "calendar"], ["/tasks/templates", "Görev Şablonları", "list"],
  ]),
  ...define("Bakım", [
    ["/maintenance", "Bakım Merkezi", "dashboard"], ["/maintenance/plans", "Bakım Planları", "list"],
    ["/maintenance/plans/main-engine", "Ana Makine Bakım Planı", "detail"], ["/maintenance/work-orders", "İş Emirleri", "list"],
    ["/maintenance/work-orders/wo-1048", "İş Emri WO-1048", "detail"], ["/maintenance/work-orders/new", "Yeni İş Emri", "form"],
    ["/maintenance/history", "Bakım Geçmişi", "list"], ["/maintenance/faults", "Arıza Kayıtları", "list"],
    ["/maintenance/meters", "Sayaçlar ve Çalışma Saatleri", "list"],
  ]),
  ...define("Stok", [
    ["/inventory", "Stok Merkezi", "dashboard"], ["/inventory/items", "Stok Ürünleri", "list"],
    ["/inventory/items/engine-oil", "Motor Yağı 15W-40", "detail"], ["/inventory/movements", "Stok Hareketleri", "list"],
    ["/inventory/locations", "Depolar", "list"], ["/inventory/counts", "Stok Sayımı", "list"],
    ["/inventory/alerts", "Minimum Stok Uyarıları", "list"], ["/inventory/categories", "Stok Kategorileri", "settings"],
  ]),
  ...define("Satın Alma", [
    ["/purchases/requests", "Satın Alma Talepleri", "list"], ["/purchases/requests/pr-248", "Talep PR-248", "detail"],
    ["/purchases/orders", "Siparişler", "list"], ["/purchases/orders/po-183", "Sipariş PO-183", "detail"],
    ["/suppliers", "Tedarikçiler", "list"], ["/suppliers/marin-teknik", "Marin Teknik", "detail"],
    ["/purchases/quotations", "Teklif Karşılaştırma", "report"],
  ]),
  ...define("Finans", [
    ["/finance", "Finans Merkezi", "dashboard"], ["/finance/expenses", "Giderler", "list"], ["/finance/expenses/exp-392", "Gider EXP-392", "detail"],
    ["/finance/expenses/new", "Yeni Gider", "form"], ["/finance/budgets", "Bütçeler", "report"], ["/finance/payments", "Ödemeler", "list"],
    ["/finance/invoices", "Faturalar", "list"], ["/finance/reports", "Finans Raporları", "report"],
  ]),
  ...define("Belgeler", [
    ["/documents", "Belge Merkezi", "list"], ["/documents/insurance-2026", "Sigorta Poliçesi", "detail"],
    ["/documents/expiring", "Süresi Yaklaşan Belgeler", "list"], ["/documents/categories", "Belge Kategorileri", "settings"],
  ]),
  ...define("Seyir ve Yakıt", [
    ["/logs/voyages", "Seyir Kayıtları", "list"], ["/logs/voyages/gocek-bedri-rahmi", "Göcek — Bedri Rahmi", "detail"],
    ["/logs/captain", "Kaptan Günlüğü", "list"], ["/logs/fuel", "Yakıt Kayıtları", "list"],
    ["/logs/fuel/new", "Yeni Yakıt İkmali", "form"], ["/logs/tanks", "Tank Seviyeleri", "dashboard"],
  ]),
  ...define("Raporlar", [
    ["/reports", "Rapor Merkezi", "report"], ["/reports/maintenance", "Bakım Raporu", "report"],
    ["/reports/inventory", "Stok Raporu", "report"], ["/reports/crew", "Personel Raporu", "report"],
    ["/reports/finance", "Finans Raporu", "report"], ["/reports/operations", "Operasyon Raporu", "report"],
  ]),
  ...define("Kullanıcı ve Yetki", [
    ["/settings/users", "Kullanıcılar", "list"], ["/settings/users/ekrem", "Ekrem Şen", "detail"],
    ["/settings/users/invite", "Kullanıcı Davet Et", "form"], ["/settings/roles", "Roller", "list"],
    ["/settings/roles/captain", "Captain Rolü", "settings"], ["/settings/audit-logs", "Aktivite Kayıtları", "list"],
  ]),
  ...define("Ayarlar", [
    ["/settings/organization", "Organizasyon Profili", "settings"], ["/settings/memberships", "Yat ve Üyelikler", "settings"],
    ["/settings/profile", "Profilim", "settings"], ["/settings/notifications", "Bildirim Tercihleri", "settings"],
    ["/settings/localization", "Dil ve Bölge", "settings"], ["/settings/currencies", "Para Birimleri", "settings"],
    ["/settings/categories", "Kategori Ayarları", "settings"], ["/settings/integrations", "Entegrasyonlar", "settings"],
  ]),
];

export const screenByPath = new Map(screens.map((screen) => [screen.path, screen]));

export const navigation = [
  { label: "Genel Bakış", href: "/dashboard", icon: "LayoutDashboard" },
  { label: "Filom", href: "/yachts", icon: "Ship" },
  { label: "Görevler", href: "/tasks", icon: "ListChecks" },
  { label: "Bakım", href: "/maintenance", icon: "Wrench" },
  { label: "Mürettebat", href: "/crew", icon: "Users" },
  { label: "Stok", href: "/inventory", icon: "Boxes" },
  { label: "Satın Alma", href: "/purchases/requests", icon: "ShoppingCart" },
  { label: "Finans", href: "/finance", icon: "WalletCards" },
  { label: "Belgeler", href: "/documents", icon: "Files" },
  { label: "Seyir ve Yakıt", href: "/logs/voyages", icon: "Route" },
  { label: "Raporlar", href: "/reports", icon: "ChartNoAxesCombined" },
  { label: "Ayarlar", href: "/settings/organization", icon: "Settings" },
];

export const screenCount = screens.length;
