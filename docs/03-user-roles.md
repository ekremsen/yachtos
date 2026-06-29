# YachtOS — Kullanıcı Rolleri ve İzin Matrisi

> **Versiyon:** 1.0  
> **Durum:** İlk sürüm — uygulama kodu öncesi referans doküman

## Amaç

Bu doküman, YachtOS’taki tüm kullanıcı rollerini, erişebilecekleri modülleri ve kayıt bazında yapabilecekleri işlemleri (Create / Read / Update / Delete) tanımlar. Uygulama geliştirilirken Laravel Policy’leri, frontend `PermissionGate` bileşenleri ve API yetkilendirmesi bu matrise göre uygulanır.

## Temel Kavramlar

### Erişim kapsamları

| Kapsam | Açıklama |
|--------|----------|
| **Platform** | Tüm tenant’lar; yalnızca Super Admin |
| **Tenant** | Kullanıcının bağlı olduğu şirket/filo — tüm yatlar |
| **Yacht (Atanmış)** | Kullanıcının `yacht_user` ataması ile bağlı olduğu bir veya birden fazla yat |
| **Self** | Yalnızca kullanıcının kendi kayıtları (ör. kendi oluşturduğu bakım talebi) |
| **Charter (Atanmış)** | Charter Manager ve Guest için belirli charter/rezervasyon kapsamı |

### İzin kısaltmaları

| Sembol | Anlam |
|--------|-------|
| **C** | Create (oluştur) |
| **R** | Read (görüntüle / listele) |
| **U** | Update (güncelle) |
| **D** | Delete (sil / arşivle) |
| **—** | Erişim yok |
| **R\*** | Kısıtlı okuma (hassas alanlar gizli) |
| **C\*** | Kısıtlı oluşturma (yalnızca belirli alt tipler) |

### Güvenlik kuralları (tüm roller)

1. **Tenant izolasyonu:** Tenant rolü dışındaki tüm roller yalnızca kendi `tenant_id` verisine erişir. Başka tenant’a ait kaynak ID’si ile istek → **404 Not Found**.
2. **Tenant bağlamı client’tan alınmaz;** oturumdaki kullanıcıdan türetilir.
3. **Yat kapsamı:** Captain, Engineer, Crew, Stewardess rolleri policy katmanında `assigned yacht_ids` ile filtrelenir.
4. **Silme:** Finans, bakım ve envanter kayıtları MVP’de soft delete; Owner dışı rollerde delete genelde kısıtlıdır.
5. **Super Admin:** Operasyonel tenant verisine yalnızca destek amaçlı, audit log ile erişir (MVP’de console/seed; UI Faz 2).

### Modül listesi

| Modül | Slug | MVP |
|-------|------|-----|
| Dashboard | `dashboard` | ✓ |
| Tenant / Şirket Ayarları | `tenant` | ✓ |
| Kullanıcı, Rol ve İzinler | `users` | ✓ |
| Yat Yönetimi | `yachts` | ✓ |
| Mürettebat / Personel | `crew` | ✓ |
| Bakım Yönetimi | `maintenance` | ✓ |
| Envanter Yönetimi | `inventory` | ✓ |
| Finans | `finance` | ✓ |
| Charter Yönetimi | `charter` | Kısmi (rol tanımı hazır; modül Faz 2) |
| Misafir Portalı | `guest_portal` | Kısmi (rol tanımı hazır; modül Faz 2) |
| Platform Yönetimi | `platform` | Super Admin only |

---

## Rol Tanımları

### 1. Super Admin

**Açıklama:** YachtOS platform operatörü. SaaS altyapısını, tenant yaşam döngüsünü ve destek operasyonlarını yönetir. Günlük yat operasyonlarına müdahale etmez; acil destek senaryolarında salt okunur veya sınırlı yazma erişimi audit log ile kayıt altına alınır.

**Kapsam:** Platform (tüm tenant’lar)

**Ne görebilir:**
- Tüm tenant listesi, durumları, abonelik bilgisi (Faz 2)
- Tenant bazında salt okunur özet metrikler (yat sayısı, kullanıcı sayısı)
- Platform audit log ve sistem sağlık göstergeleri

**Ne yapabilir (CRUD):**
- Tenant: **CRUD** (oluştur, askıya al, sil)
- Platform ayarları: **CRUD**
- Tenant içi operasyonel kayıtlar (yat, bakım, finans vb.): **R** (destek modu; varsayılan — yazma kapalı)

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Platform Yönetimi | Tam |
| Tenant listesi / provisioning | Tam |
| Dashboard (tenant) | — |
| Tenant / Users / Yachts / Crew / Maintenance / Inventory / Finance | Salt okunur (destek) |
| Charter / Guest Portal | — |

---

### 2. Tenant Owner

**Açıklama:** Kiracı şirketin en üst düzey yöneticisi. Filo veya yat sahibi organizasyonunu temsil eder. Kullanıcı ve rol yönetimi, şirket ayarları, tüm modüllere tam erişim ve silme yetkisine sahiptir.

**Kapsam:** Tenant (tüm yatlar)

**Ne görebilir:**
- Tenant içindeki tüm modüller ve kayıtlar
- Tüm finansal veriler, maliyet detayları, kullanıcı listesi ve rol atamaları
- Dashboard: filo geneli özet

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Tenant ayarları | — | ✓ | ✓ | — |
| Kullanıcılar ve roller | ✓ | ✓ | ✓ | ✓ |
| Yatlar | ✓ | ✓ | ✓ | ✓ |
| Mürettebat | ✓ | ✓ | ✓ | ✓ |
| Bakım kayıtları | ✓ | ✓ | ✓ | ✓ |
| Envanter | ✓ | ✓ | ✓ | ✓ |
| Finans | ✓ | ✓ | ✓ | ✓ |
| Charter (Faz 2) | ✓ | ✓ | ✓ | ✓ |

**Modül erişimi:** Tüm tenant modülleri — **tam erişim**

---

### 3. Fleet Manager

**Açıklama:** Filo operasyonlarından sorumlu yönetici. Tüm yatlar üzerinde operasyonel tam yetki; personel koordinasyonu, bakım, envanter ve finans yönetimi. Tenant ayarları ve kullanıcı/rol yönetimine erişemez.

**Kapsam:** Tenant (tüm yatlar)

**Ne görebilir:**
- Tüm yatlar, mürettebat, bakım, envanter, finans
- Dashboard: filo geneli
- Kullanıcı listesi: **R\*** (yalnızca operasyonel roller; rol değiştiremez)

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Tenant ayarları | — | R* | — | — |
| Kullanıcılar | — | ✓ | — | — |
| Yatlar | ✓ | ✓ | ✓ | ✓ |
| Mürettebat | ✓ | ✓ | ✓ | ✓ |
| Bakım | ✓ | ✓ | ✓ | ✓ |
| Envanter | ✓ | ✓ | ✓ | ✓ |
| Finans | ✓ | ✓ | ✓ | U* |
| Charter (Faz 2) | ✓ | ✓ | ✓ | — |

*Finans delete: soft delete yok; iptal/ters kayıt ile düzeltme. Tenant ayarları: yalnızca para birimi/timezone okuma.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ |
| Tenant ayarları | — |
| Users / Roles | R (liste) |
| Yachts | Tam |
| Crew | Tam |
| Maintenance | Tam |
| Inventory | Tam |
| Finance | Tam |
| Charter | Tam (Faz 2) |

---

### 4. Captain

**Açıklama:** Atandığı yat(lar)ın operasyonel sorumlusu. Seyir, mürettebat koordinasyonu, bakım talepleri, envanter takibi ve sınırlı finansal görünürlük. Yat dışı filo kayıtlarına erişemez.

**Kapsam:** Yacht (atanmış)

**Ne görebilir:**
- Atandığı yat(lar): detay, mürettebat, bakım, envanter
- Finans: **R\*** (yalnızca atandığı yat, özet ve bakım maliyetleri; maaş/hassas kalemler gizli)
- Dashboard: atanmış yat(lar) özeti

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar (atanmış) | — | ✓ | U* | — |
| Mürettebat (atanmış yat) | ✓ | ✓ | ✓ | — |
| Bakım | ✓ | ✓ | ✓ | — |
| Envanter | ✓ | ✓ | ✓ | — |
| Finans | — | R* | — | — |
| Kullanıcı davet | — | — | — | — |

*Yat güncelleme: operasyonel alanlar (durum, notlar); yapısal alanlar (IMO, boy) — Owner/Fleet Manager.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (scoped) |
| Tenant / Users | — |
| Yachts | RU (assigned) |
| Crew | CRU (assigned) |
| Maintenance | CRU (assigned) |
| Inventory | CRU (assigned) |
| Finance | R (assigned, limited) |
| Charter | R (assigned charter) |

---

### 5. Engineer

**Açıklama:** Teknik departman; makine, elektrik ve yapısal bakım ile envanterden sorumlu. Atandığı yat(lar)da bakım kayıtları ve stok yönetiminde tam operasyonel yetki. Finans ve personel yönetimine erişimi yoktur.

**Kapsam:** Yacht (atanmış)

**Ne görebilir:**
- Atandığı yat(lar): teknik detay, bakım geçmişi, envanter
- Mürettebat listesi: **R** (iletişim amaçlı, atanmış yat)
- Finans: **—** (MVP); ileride yalnızca bakım maliyeti **R\***

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar | — | ✓ | — | — |
| Mürettebat | — | ✓ | — | — |
| Bakım | ✓ | ✓ | ✓ | — |
| Envanter | ✓ | ✓ | ✓ | U* |
| Finans | — | — | — | — |

*Envanter delete: düşük; stok sıfırlama adjustment ile.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (scoped, teknik KPI) |
| Yachts | R (assigned) |
| Crew | R (assigned) |
| Maintenance | CRU (assigned) |
| Inventory | CRU (assigned) |
| Finance | — |
| Charter | — |

---

### 6. Crew

**Açıklama:** Genel mürettebat (deck crew vb.). Atandığı yatın operasyonel bilgilerini görür; bakım talebi açabilir, envanteri okur. Kayıt oluşturma ve güncelleme yetkileri dar kapsamlıdır.

**Kapsam:** Yacht (atanmış) + Self

**Ne görebilir:**
- Atandığı yat: genel bilgi, vardiya/görev (Faz 2), envanter listesi
- Bakım: **R** (atanmış yat, genel durum)
- Kendi oluşturduğu bakım talepleri: tam detay
- Finans, kullanıcılar, diğer yatlar: **—**

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar | — | ✓ | — | — |
| Mürettebat | — | R* | — | — |
| Bakım | C* | R | U* | — |
| Envanter | — | ✓ | — | — |
| Finans | — | — | — | — |

* Bakım create: yalnızca `type=breakdown` veya `request` alt tipi, `status=open`.  
* Bakım update: yalnızca kendi oluşturduğu ve `status=open` kayıtlar.  
* Mürettebat: yalnızca iletişim alanları (telefon maskeli olabilir).

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (limited widgets) |
| Yachts | R (assigned) |
| Crew | R* (assigned) |
| Maintenance | CR* / R / U* (self, open) |
| Inventory | R (assigned) |
| Finance | — |

---

### 7. Stewardess

**Açıklama:** İç hizmetler ve konuk deneyimi (interior, housekeeping, galley destek). Envanterde tüketim malzemeleri, tekstil ve provizyon kalemlerini yönetir; interior kaynaklı bakım talepleri açar.

**Kapsam:** Yacht (atanmış)

**Ne görebilir:**
- Atandığı yat: genel bilgi, interior envanter kategorileri
- Mürettebat: **R\*** (hizmet koordinasyonu için sınırlı)
- Bakım: interior / housekeeping ile ilgili kayıtlar
- Finans: **—**

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar | — | ✓ | — | — |
| Mürettebat | — | R* | — | — |
| Bakım | C* | R | U* | — |
| Envanter | C* | ✓ | ✓ | — |
| Finans | — | — | — | — |

* Bakım create: `category=interior|housekeeping|galley`.  
* Envanter create/update: yalnızca `category` ∈ {provisions, linens, interior, galley_consumables}.  
* Stok hareketi (çıkış): ✓.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (scoped, interior/low stock) |
| Yachts | R (assigned) |
| Crew | R* |
| Maintenance | C*/RU* (interior scope) |
| Inventory | CRU* (category-scoped) |
| Finance | — |
| Charter | R* (misafir listesi, konaklama — Faz 2) |

---

### 8. Accountant

**Açıklama:** Muhasebe ve finans sorumlusu. Gelir/gider kayıtları, kategoriler, dönem özetleri ve raporlar üzerinde tam yetki. Operasyonel modüllerde salt okunur erişim; kayıt silme yerine ters kayıt / iptal akışı.

**Kapsam:** Tenant (tüm yatlar — finans); operasyonel modüller salt okunur

**Ne görebilir:**
- Tüm finans kayıtları ve özetler
- Yat listesi, bakım maliyetleri, envanter değerleri (maliyet hesabı için)
- Mürettebat: **R\*** (bordro entegrasyonu öncesi yalnızca pozisyon/ad; maaş alanları Faz 2)
- Dashboard: finans ağırlıklı widget’lar

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar | — | ✓ | — | — |
| Mürettebat | — | R* | — | — |
| Bakım | — | ✓ | — | — |
| Envanter | — | ✓ | — | — |
| Finans | ✓ | ✓ | ✓ | U* |
| Tenant ayarları | — | R* | — | — |

* Finans delete: doğrudan silme yok; `status=voided` ile iptal. Para birimi ayarı okunur.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (finance-focused) |
| Tenant | R* |
| Users | — |
| Yachts | R |
| Crew | R* |
| Maintenance | R |
| Inventory | R |
| Finance | CRU (no hard delete) |
| Charter | R (gelir kalemleri — Faz 2) |

---

### 9. Charter Manager

**Açıklama:** Charter (kiralama) operasyonlarından sorumlu. Rezervasyon, takvim, fiyatlandırma, misafir manifestosu ve charter gelir kalemlerini yönetir. Günlük teknik bakım ve mühendislik envanterine müdahale etmez.

**Kapsam:** Tenant (charter modülü); operasyonel yat verisi salt okunur

**Ne görebilir:**
- Charter takvimi, rezervasyonlar, misafir listeleri
- Yatlar: müsaitlik ve temel özellikler (**R**)
- Finans: charter gelir/gider kalemleri (**R** + charter kategorisinde **CRU**)
- Bakım: **R\*** (charter öncesi/sonrası planlama için durum özeti)

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Yatlar | — | ✓ | — | — |
| Charter / Rezervasyon | ✓ | ✓ | ✓ | ✓ |
| Misafir (Guest) davet | ✓ | ✓ | ✓ | ✓ |
| Finans (charter) | ✓ | ✓ | ✓ | U* |
| Bakım | C* | R* | — | — |
| Mürettebat | — | R* | — | — |
| Envanter | — | — | — | — |

* Bakım create: `type=planned`, charter hazırlık checklist bağlantılı.  
* Finans: yalnızca `category` charter ile ilişkili kalemler.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | ✓ (charter KPI) |
| Yachts | R |
| Crew | R* |
| Maintenance | R / C* (charter prep) |
| Inventory | — |
| Finance | CRU* (charter scope) |
| Charter | Tam (Faz 2) |
| Guest Portal yönetimi | ✓ |

> **Not:** Charter modülü MVP kapsamı dışındadır; rol ve izinler Faz 2 implementasyonu için önceden tanımlanmıştır.

---

### 10. Guest

**Açıklama:** Charter misafiri veya davetli ziyaretçi. Yalnızca kendi charter/rezervasyon kapsamındaki sınırlı bilgileri görür. Operasyonel, finansal ve personel verisine erişemez.

**Kapsam:** Charter (atanmış rezervasyon) + Guest Portal

**Ne görebilir:**
- Kendi rezervasyon detayı: tarihler, yat adı, liman/rota özeti (Faz 2)
- Genel gemi bilgisi: güvenlik brifingi, Wi-Fi, kurallar (statik içerik)
- İletişim: kaptan/stewardess iletişim noktası (maskeli)
- Bakım, envanter, finans, mürettebat listesi: **—**

**Ne yapabilir (CRUD):**

| Kaynak | C | R | U | D |
|--------|---|---|---|---|
| Rezervasyon (kendi) | — | ✓ | U* | — |
| Tercih / özel istek notu | C | ✓ | ✓ | — |
| Tüm operasyonel kayıtlar | — | — | — | — |

* Rezervasyon update: yalnızca misafir tercih alanları (diyet, transfer); tarih değişikliği — Charter Manager onayı gerekir.

**Modül erişimi:**

| Modül | Erişim |
|-------|--------|
| Dashboard | — (Guest Portal ana sayfa) |
| Guest Portal | R / CU (preferences) |
| Yachts | R* (public profile only) |
| Crew / Maintenance / Inventory / Finance | — |
| Charter | R (own booking) |

> **Not:** Guest hesapları genelde davet linki ile oluşturulur; süresi rezervasyon bitişinde otomatik devre dışı bırakılır.

---

## Modül Erişim Matrisi (Özet)

| Modül | Super Admin | Owner | Fleet Mgr | Captain | Engineer | Crew | Stewardess | Accountant | Charter Mgr | Guest |
|-------|:-----------:|:-----:|:---------:|:-------:|:--------:|:----:|:----------:|:----------:|:-----------:|:-----:|
| Platform | ✓ | — | — | — | — | — | — | — | — | — |
| Dashboard | — | ✓ | ✓ | ✓* | ✓* | ✓* | ✓* | ✓* | ✓* | — |
| Tenant ayarları | ✓ | ✓ | — | — | — | — | — | R* | — | — |
| Users / Roles | — | ✓ | R | — | — | — | — | — | — | — |
| Yachts | R | ✓ | ✓ | ✓* | ✓* | ✓* | ✓* | R | R | R* |
| Crew | R | ✓ | ✓ | ✓* | R | R* | R* | R* | R* | — |
| Maintenance | R | ✓ | ✓ | ✓* | ✓* | ✓* | ✓* | R | R/C* | — |
| Inventory | R | ✓ | ✓ | ✓* | ✓* | R | ✓* | R | — | — |
| Finance | R | ✓ | ✓ | R* | — | — | — | ✓ | ✓* | — |
| Charter | — | ✓ | ✓ | R* | — | — | R* | R* | ✓ | R* |
| Guest Portal | — | ✓ | R | — | — | — | R* | — | ✓ | ✓ |

**✓** = tam erişim · **✓\*** = kapsamlı/sınırlı · **R** = salt okunur · **—** = erişim yok

---

## CRUD Matrisi (Kaynak Bazlı Özet)

| Kaynak | Super Admin | Owner | Fleet Mgr | Captain | Engineer | Crew | Stewardess | Accountant | Charter Mgr | Guest |
|--------|:-----------:|:-----:|:---------:|:-------:|:--------:|:----:|:----------:|:----------:|:-----------:|:-----:|
| **Tenant settings** | CRUD | RU | — | — | — | — | — | R | — | — |
| **Users / Roles** | CRUD | CRUD | R | — | — | — | — | — | — | — |
| **Yachts** | R | CRUD | CRUD | RU* | R | R | R | R | R | R* |
| **Crew** | R | CRUD | CRUD | CRU | R | R* | R* | R* | R* | — |
| **Maintenance** | R | CRUD | CRUD | CRU | CRU | C*/RU* | C*/RU* | R | R/C* | — |
| **Inventory** | R | CRUD | CRUD | CRU | CRU | R | CRU* | R | — | — |
| **Finance** | R | CRUD | CRU* | R* | — | — | — | CRU* | CRU* | — |
| **Charter** | R | CRUD | CRU | R* | — | — | R* | R* | CRUD | R/U* |

---

## Yat Ataması ve Policy Kuralları

```
Her istek:
  1. tenant_id === auth.user.tenant_id     → değilse 404
  2. rol yacht-scoped mi?
       → evet: resource.yacht_id ∈ auth.user.assignedYachtIds()
       → hayır: tenant geneli devam
  3. permission slug kontrolü (ör. maintenance.create)
  4. kaynak bazlı ek kural (category scope, self-only, status guard)
```

### Örnek policy kontrolleri

| Senaryo | Sonuç |
|---------|-------|
| Crew, başka yatın envanterini listeler | 403 veya boş liste (scope) |
| Engineer, finans API’sine istek atar | 403 |
| Captain, atanmadığı yatın bakımını günceller | 404 |
| Guest, başka misafirin rezervasyonunu okur | 404 |
| Fleet Manager, Owner rolünü kendine atar | 403 |
| Accountant, bakım kaydını siler | 403 (void finans kaydı ayrı akış) |

---

## MVP vs Sonraki Fazlar

| Rol | MVP’de aktif | Not |
|-----|:------------:|-----|
| Super Admin | Kısmi | Console/seed; platform UI Faz 2 |
| Tenant Owner | ✓ | Kayıt akışında varsayılan rol |
| Fleet Manager | ✓ | |
| Captain | ✓ | `yacht_user` ataması zorunlu |
| Engineer | ✓ | |
| Crew | ✓ | |
| Stewardess | ✓ | Envanter kategori scope MVP’de basit enum |
| Accountant | ✓ | |
| Charter Manager | — | Rol seed’de tanımlı; modül Faz 2 |
| Guest | — | Davet portalı Faz 2 |

---

## Uygulama Notları (Kod Öncesi)

1. **Permission slug formatı:** `{module}.{action}` — ör. `maintenance.create`, `finance.read`, `inventory.update`
2. **Rol slug’ları:** `super_admin`, `tenant_owner`, `fleet_manager`, `captain`, `engineer`, `crew`, `stewardess`, `accountant`, `charter_manager`, `guest`
3. **Spatie Permission:** roller `tenant_id` ile scope’lanır; permission tanımları global, rol-permission eşlemesi tenant başına seed edilir
4. **Frontend:** menü ve butonlar permission slug ile gizlenir; API yine policy ile korunur (UI-only güvenlik yeterli değil)
5. **Özel kısıtlar** (category scope, self-only): policy sınıfında `before` / `create` / `update` metodlarında uygulanır; permission tablosuna taşınmaz (MVP)

---

## Sürüm Geçmişi

| Versiyon | Tarih | Değişiklik |
|----------|-------|------------|
| 1.0 | 2025-06-29 | İlk rol ve izin matrisi — 10 rol |
