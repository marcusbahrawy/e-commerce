# Motorleaks.no — Høyytelses nettbutikk

**Mål:** Bygge en ekstremt rask, modulær og sikker e-commerce-løsning på ren LAMP-stack (Linux/Apache/MySQL/PHP 8+) med moderne UI/UX, uten tunge rammeverk.

---

## Implementasjonsstatus (MVP)

**Public:** Forside, katalog (kategorier, produktside), søk (`/sok`), CMS-sider (`/side/{slug}`), handlekurv, kasse med Stripe Checkout, sitemap.xml.  
**Konto:** Innlogging, registrering, glemt passord, tilbakestill passord, profilredigering, bytt passord, ordreliste og ordredetalj.  
**Admin:** Innlogging, dashboard, produkter (CRUD + bildeopplasting), kategorier, CMS-sider, menyer, fraktmetoder, ordrer (liste + detalj + status/fulfillment).  
**Betaling:** Stripe Checkout Session; webhook for `checkout.session.completed` (oppdaterer ordre til betalt, sender ordrebekreftelse på e-post).  
**E-post:** Ordrebekreftelse ved betalt ordre (webhook); glemt passord (PHP `mail()`). Avsender: `MAIL_FROM` i `.env`.  
**Feilsider:** Egne 404-mal (`app/Templates/errors/404.php`).

**Ikke implementert (fra spec):** 2FA admin, rate limiting, APCu/filcache, REST API-lag, full audit logg, megameny, typeahead-søk, bildepipeline (WebP batch), 301-redirect-tabell, unit/integrasjonstester i CI.

---

**Ytelsesmål:**
- Lastetid &lt; 1,0 s for cachede sider
- Lastetid &lt; 2,0 s for ikke-cachede dynamiske flows (på realistiske mobile nett)

**Domene:** motorleaks.no (dagens side har kategoritunge «produktkategori/…»-strukturer og WP-lignende navigasjon/innhold).

### Kjøring lokalt

1. **PHP 8.2+** og Composer på plass.
2. `composer install` i prosjektrot.
3. Kopier `.env.example` til `.env` og fyll inn `APP_URL` og **database** (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. Opprett databasen (f.eks. `CREATE DATABASE motorleaks;`) og kjør migrasjoner:  
   `php bin/migrate.php`  
   Deretter (valgfritt) testdata:  
   `php bin/seed.php`
5. **Stripe (valgfritt):** Fyll inn `STRIPE_KEY`, `STRIPE_SECRET` og `STRIPE_WEBHOOK_SECRET` i `.env` for betaling. Uten Stripe sendes kunden direkte til takk-siden etter bestilling.
6. **PHP innebygd server:**  
   `php -S localhost:8000 -t public public/router.php`  
   Åpne http://localhost:8000
7. **Apache:** Sett document root til `public/`, mod_rewrite: alle ikke-eksisterende filer → `index.php`.

---

## 0. Ikke-funksjonelle hovedkrav (absolutte)

### 0.1 Ytelse (hard requirements)

| Krav | Verdi |
|------|--------|
| TTFB (cache hit) | ≤ 80 ms (server-side) |
| TTFB (cache miss, PHP render) | ≤ 250 ms (median) |
| LCP (mobil, 4G-lignende) | ≤ 1,8 s (median) |
| CLS | ≤ 0,05 |
| INP | ≤ 150 ms (median) |
| HTML størrelse forsiden | ≤ 60 KB gzip |
| HTML størrelse kategorisider | ≤ 80 KB gzip |
| HTML størrelse produktsider | ≤ 90 KB gzip (uten bilder) |
| Antall requests (første visning) | ≤ 35 (inkl. bilder) |

**DB queries pr. side:**
- Forside: ≤ 10 (cache miss), ≤ 2 (cache hit)
- Kategori: ≤ 12 (cache miss), ≤ 3 (cache hit)
- Produkt: ≤ 14 (cache miss), ≤ 4 (cache hit)

- Ingen runtime-kompilering (ingen store templatemotorer)
- Aggressiv caching på flere nivåer

### 0.2 Sikkerhet (hard requirements)

- All DB-tilgang via **PDO prepared statements** (obligatorisk)
- **CSRF-token** på alle muterende requests (POST/PUT/PATCH/DELETE)
- **XSS-sikring:** output-escaping som standard + HTML whitelist der nødvendig
- Streng inputvalidering med eksplisitte schema per endpoint/form
- **Passord:** `password_hash()` (Argon2id hvis tilgjengelig), `password_verify()`
- **Admin:** 2FA-støtte (TOTP) + IP rate limiting + audit logg
- Sikker session-håndtering (HttpOnly, Secure, SameSite, session rotation)

### 0.3 Arkitektur (hard requirements)

- **Modular monolith** på PHP 8+ (OOP), MVC-inspirert, tydelig separasjon:
  - HTTP (routing/middleware)
  - Controllers (input → use-case)
  - Services/Use-cases (forretningslogikk)
  - Repositories (DB)
  - Templates (view)
  - Domain models/DTOs
- **REST API-lag** for frontend interaksjoner (cart, checkout, account, admin)
- **Ingen WordPress, ingen tunge rammeverk**

---

## 1. Systemoversikt og arkitektur

### 1.1 Komponenter

1. Apache (primært direkte PHP-FPM via proxy_fcgi)
2. PHP-FPM (PHP 8.2/8.3)
3. MySQL 8 (InnoDB)
4. Filcache (disk) + applikasjonscache (APCu)
5. Bildepipeline (offline batch + on-demand thumbnails med kø/cron)
6. Adminpanel (samme app, `/admin`, RBAC)
7. Webhook-endpoint (Stripe) med signaturvalidering

### 1.2 Modulær monolitt: mappe-/pakke-struktur

```
/var/www/motorleaks
  /public
    index.php
    /assets     (byggede, minifiserte CSS/JS)
    /img        (statisk, optimalisert)
    /media      (opplastede bilder, produktbilder)
    /cache      (page cache fragments)
  /app
    /Http       (Router, Request, Response, Middleware)
    /Controllers (Home, Catalog, Product, Cart, Checkout, Account, Cms, Api/*, Admin/*)
    /Domain     (Models, DTO, ValueObjects, Exceptions)
    /Services   (Catalog, Pricing, Inventory, Cart, Checkout, Order, Payment, …)
    /Repositories
    /Templates  (layout, partials, pages, components)
    /Validation (Schemas, Validator)
    /Config
    /Support    (Env, Helpers, Html, Csrf, Auth, Pagination, Money, Slug)
  /storage     (utenfor webroot: logs, sessions, cache, exports, imports, mail)
  /bin         (cron, migrate, seed, image_jobs)
  /migrations
  /tests
  composer.json
```

### 1.3 Request-livssyklus (server-side)

1. Apache → `/public/index.php`
2. Bootstrap: last .env, init config, DB pool (PDO), router
3. Middleware-kjede: SecurityHeaders → RateLimit → Session → Csrf → Auth → Cache
4. Controller → Service → Repository
5. Template render (minimal engine) → Response
6. Logging (structured) + metrics (timings)

---

## 2. Apache-konfigurasjon

- **HTTP/2**, gzip/brotli, cache headers, strict TLS
- Sikkerhetsheadere: HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, CSP
- Block tilgang til: `/app`, `/storage`, `/migrations`, `/tests`, `.env`, `composer.*`
- **Rewrite:** rene URLer; alle ikke-eksisterende filer → `index.php`
- 301 fra gamle WP/legacy stier til nye canonical

**URL-struktur:**
- Forside: `/`
- Kategori: `/kategori/{slug}` eller hierarkisk `/kategori/{parent}/{child}`
- Produkt: `/produkt/{slug}` eller `/p/{id}-{slug}`
- CMS: `/side/{slug}` eller direkte slug (om-oss, kjopsbetingelser, …)
- Konto: `/konto/...` | Admin: `/admin/...` | API: `/api/...` | Webhook: `/webhooks/stripe`

---

## 3. PHP backend-arkitektur (MVC-inspirert)

- **Routing:** Egen Router med GET/POST/PUT/PATCH/DELETE, route params, named routes, middleware per route group
- **Controllers:** Tynne — parse/valider input, kall service, returner view/json/redirect
- **Services:** Prisberegning, lager (reservering i checkout), ordrestatus, e-post, caching-beslutninger; transaksjoner for ordre/lager
- **Repositories:** Kun SQL + mapping til DTO/Models; parametrisert SQL; begrens SELECT; bruk indekser
- **Templates:** Ren PHP + partials; én `layout.php` med slots (head, header, main, footer); helpers: `e()`, `url()`, `asset()`, `csrf_field()`

---

## 4. API-lag

- **Format:** JSON-only; envelope `{"ok":true,"data":...,"meta":...}`; feil med `code`, `message`, `fields`
- **Public/API:** catalog/suggest, cart (POST/PATCH/DELETE/GET), checkout (start, address, shipping-method, confirm), order, account (login/logout/register, password forgot/reset)
- **Admin/API:** dashboard, products, categories, attributes, orders, menus, pages, email-templates, settings, cache (purge/warm), logs, stats
- **Stripe webhooks:** POST `/webhooks/stripe` med signaturvalidering, idempotency (event_id), håndter payment_intent.succeeded/failed, checkout.session.completed, charge.refunded

---

## 5. MySQL database design

- **InnoDB, UTF8MB4.** Alle tabeller: `id` BIGINT UNSIGNED AUTO_INCREMENT, `created_at`/`updated_at` DATETIME(6)
- **Penger:** int i øre (NOK). **Slugs:** unik per scope. **Soft delete** der nødvendig via `deleted_at`

**Hovedområder:** Produkter/katalog (products, brands, categories, product_categories, product_images, attributes, product_variants, …), Lager (inventory_items, inventory_movements), Brukere/auth (users, roles, permissions, user_roles, user_addresses), Handlekurv/checkout (carts, cart_items, checkout_sessions), Ordrer (orders, order_items, order_payments, order_status_history), Menyer/CMS (menus, menu_items, pages), Innstillinger/frakt/e-post (settings, shipping_methods, email_templates), Logg/audit (logs, audit_logs)

**Indekser:** slug UNIQUE der relevant; (status, created_at); (user_id, created_at); category_id/product_id; variant_id/product_id for lager.

---

## 6. Ytelse: caching og ressurser

- **OPcache:** enable, validate_timestamps=0 i prod
- **APCu:** hot data (settings, menytre, category tree)
- **Filcache:** page cache for GET (forside, kategori, produkt, cms); fragment cache (header/footer/meny/breadcrumbs)
- **HTTP cache:** immutable assets; Cache-Control på media
- **Page cache:** Cachebar `/`, `/kategori/*`, `/produkt/*`, `/side/*`; ikke cachebar handlekurv/kasse/konto/admin
- **Invalidering:** Produkt/kategori/CMS oppdatert → purge relevante URLer
- **HTML/Head:** Critical CSS inline; resten preload+async; JS oppdelt (core, catalog, product, cart, checkout), defer
- **Bilder:** WebP primær; responsive srcset; lazyload; LCP-bilde preload, ingen lazy på LCP

---

## 7. Frontend — design og komponenter

- **Design:** Premium, ryddig, høy tillit, salgsorientert; bedre navigasjon enn dagens (mindre kategoritekst, tydeligere lister)
- **Tokens:** Spacing 4/8/12/16/24/32/48/64; radius 8/12/16; skygger; typografi H1 32–40, H2 24–32, body 16–18
- **Komponenter:** Header (logo, søk typeahead, kategorinav mega-meny, konto, handlekurv mini), Product card, Filters, Breadcrumbs, Pagination, Trustbar, Footer
- **Interaksjoner:** Hover/active/loading på knapper; inline validering; mini-cart slide-in; variantvelger med tilgjengelighet; toasts
- **SEO:** Semantisk HTML5; Product/BreadcrumbList/Organization schema; canonical; meta; Open Graph; sitemap; 301 fra gamle URLer

---

## 8. Sidemaler (kort oversikt)

- **Header/Footer:** Sticky header med smart shrink; mega-meny; typeahead søk; trustbar; footer 3 kolonner + policy-lenker
- **Forside (/):** Hero, kategori-grid, utvalgte/populære produkter, trust-seksjon
- **Kategoriside (/kategori/{slug}):** Breadcrumbs, filtre, sortering, paginering, produktgrid
- **Produktside (/produkt/{slug}):** Galleri, variantvelger, pris/lager, legg i handlekurv, beskrivelse/spesifikasjoner
- **Konto (/konto):** Ordreliste/detalj, profil, adresser
- **Handlekurv (/handlekurv):** Linjer, totals, til kassen
- **Kasse (/kasse):** Steg: kundeinfo → adresse → frakt → betaling (Stripe) → bekreftelse
- **CMS:** om-oss, kjopsbetingelser, angrerett-retur, personvern

---

## 9. Adminpanel (/admin)

- **Login:** E-post + passord; 2FA TOTP; kort idle timeout; RBAC (Admin, Manager, Support, Content)
- **Audit logging** på alle mutasjoner
- **Moduler:** Dashboard (KPI, siste ordre), Produkter/Kategorier/Attributter (CRUD), Ordrer (liste, status, refund), Menybygger, CMS-sider, E-postmaler, Fraktinnstillinger, Brukeradministrasjon, Cache (purge/warm), Loggsystem, Statistikk
- **Stripe:** PaymentIntent/Checkout Session; webhooks for betaling/refund; idempotent prosessering

---

## 10. Sikkerhet (kort)

- SQL: PDO prepared statements; dynamiske placeholders for IN(...)
- XSS: Escape standard; HTML whitelist + sanitization for CMS/prod-beskrivelse
- CSRF: Token per session; roter ved login
- Auth: Passordpolicy (min 12 tegn); rate-limit login; account enumeration-beskyttelse på forgot-password
- Admin: 2FA; audit log

---

## 11. Build og filstruktur (uten tunge rammeverk)

- **CSS:** BEM eller utility-first light; SCSS valgfritt; output `app.hash.css`
- **JS:** ES modules; lett bundler (f.eks. esbuild); output `core.hash.js`, `catalog.hash.js`, `product.hash.js`, `checkout.hash.js`
- **HTML:** Server-rendered; progressive enhancement

---

## 12. Migrering

- Import scripts for produkter, kategorier, bilder, evt. kunder/ordre
- Mapping gamle kategori-URLer til nye slugs
- 301-redirects: `/produktkategori/...` → `/kategori/...`; gamle produkt-URLer → `/produkt/...`
- Redirect-tabell: `redirects(old_path, new_path, status_code, hits)`

---

## 13. Minimum leveranse (MVP, produksjonsklar)

**Public:** forside, kategori, produkt, handlekurv, checkout (Stripe), konto (ordreoversikt + detalj), CMS/policy-sider  

**Admin:** login + RBAC, produkter/kategorier/attributter, ordre, meny, CMS, fraktinnstillinger, cache purge, logs  

**Kvalitet:** Unit tests (pricing, inventory reserve/release, order state); integrasjonstester (checkout happy path, Stripe webhook idempotency); observability (request_id, timings: total, db, render)
