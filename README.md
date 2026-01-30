SPEC — Motorleaks.no (ny, skreddersydd høyytelses nettbutikk uten WordPress)
Mål: Bygge en ekstremt rask, modulær og sikker e-commerce-løsning på ren LAMP-stack (Linux/Apache/MySQL/PHP 8+) med moderne UI/UX, uten tunge rammeverk, med lastetid < 1,0 s for cachede sider og < 2,0 s for ikke-cachede dynamiske flows (på realistiske mobile nett).
Domene: motorleaks.no (dagens side har kategoritunge “produktkategori/…”-strukturer og WP-lignende navigasjon/innhold). 
________________________________________
0. Ikke-funksjonelle hovedkrav (absolutte)
0.1 Ytelse (hard requirements)
•	TTFB (cache hit): ≤ 80 ms (server-side)
•	TTFB (cache miss, PHP render): ≤ 250 ms (median)
•	LCP (mobil, 4G-lignende): ≤ 1,8 s (median)
•	CLS: ≤ 0,05
•	INP: ≤ 150 ms (median)
•	HTML størrelse: forsiden ≤ 60 KB gzip, kategorisider ≤ 80 KB gzip, produktsider ≤ 90 KB gzip (uten bilder).
•	Antall requests (første visning): ≤ 35 (inkl. bilder), cachede assets via HTTP cache.
•	DB queries pr. side:
o	Forside: ≤ 10 (cache miss), ≤ 2 (cache hit)
o	Kategori: ≤ 12 (cache miss), ≤ 3 (cache hit)
o	Produkt: ≤ 14 (cache miss), ≤ 4 (cache hit)
•	Ingen runtime-kompilering (ingen store templatemotorer).
•	Aggressiv caching på flere nivåer (se §6).
0.2 Sikkerhet (hard requirements)
•	All DB-tilgang via PDO prepared statements (obligatorisk).
•	CSRF-token på alle muterende requests (POST/PUT/PATCH/DELETE).
•	XSS-sikring: output-escaping som standard + HTML whitelist der nødvendig.
•	Streng inputvalidering med eksplisitte schema per endpoint/form.
•	Passord: password_hash() (Argon2id hvis tilgjengelig), password_verify().
•	Admin: 2FA-støtte (TOTP) + IP rate limiting + audit logg.
•	Sikker session-håndtering (HttpOnly, Secure, SameSite, session rotation).
0.3 Arkitektur (hard requirements)
•	Modular monolith på PHP 8+ (OOP), MVC-inspirert, tydelig separasjon:
o	HTTP (routing/middleware)
o	Controllers (input → use-case)
o	Services/Use-cases (forretningslogikk)
o	Repositories (DB)
o	Templates (view)
o	Domain models/DTOs
•	REST API-lag for frontend interaksjoner (cart, checkout, account, admin).
•	Ingen WordPress, ingen tunge rammeverk.
________________________________________
1. Systemoversikt og arkitektur
1.1 Komponenter
1.	Apache (reverse-proxy-funksjonalitet via mod_proxy valgfritt, men primært direkte PHP-FPM via proxy_fcgi anbefalt)
2.	PHP-FPM (PHP 8.2/8.3)
3.	MySQL 8 (InnoDB)
4.	Filcache (disk) + applikasjonscache (APCu)
5.	Bildepipeline (offline batch + on-demand thumbnails med kø/cron)
6.	Adminpanel (samme app, /admin, RBAC)
7.	Webhook-endpoint (Stripe) med signaturvalidering
1.2 Modulær monolitt: foreslått mappe-/pakke-struktur
/var/www/motorleaks
  /public
    index.php
    /assets
      /css (byggede, minifiserte)
      /js  (byggede, minifiserte)
      /img (statisk, optimalisert)
    /media (opplastede bilder, produktbilder)
    /cache (page cache fragments hvis offentlig tilgjengelig via rewrite, ellers utenfor webroot)
  /app
    /Http
      Router.php
      Request.php
      Response.php
      Middleware/
        AuthMiddleware.php
        CsrfMiddleware.php
        RateLimitMiddleware.php
        CacheMiddleware.php
    /Controllers
      HomeController.php
      CatalogController.php
      ProductController.php
      CartController.php
      CheckoutController.php
      AccountController.php
      CmsController.php
      Api/
        CartApiController.php
        CheckoutApiController.php
        AccountApiController.php
        AdminApiController.php
        StripeWebhookController.php
      Admin/
        DashboardController.php
        ProductsController.php
        CategoriesController.php
        AttributesController.php
        OrdersController.php
        MenusController.php
        PagesController.php
        EmailTemplatesController.php
        ShippingController.php
        UsersController.php
        CacheController.php
        LogsController.php
        StatsController.php
    /Domain
      Models/
      DTO/
      ValueObjects/
      Exceptions/
    /Services
      CatalogService.php
      PricingService.php
      InventoryService.php
      CartService.php
      CheckoutService.php
      OrderService.php
      PaymentService.php
      ShippingService.php
      UserService.php
      EmailService.php
      CmsService.php
      SearchService.php
      CacheService.php
      ImageService.php
      AuditService.php
      StatsService.php
    /Repositories
      ProductRepository.php
      CategoryRepository.php
      AttributeRepository.php
      InventoryRepository.php
      CartRepository.php
      OrderRepository.php
      UserRepository.php
      CmsRepository.php
      MenuRepository.php
      SettingsRepository.php
      LogRepository.php
    /Templates
      layout.php
      partials/
      pages/
      components/
    /Validation
      Schemas/
      Validator.php
    /Config
      app.php
      db.php
      cache.php
      stripe.php
      mail.php
      shipping.php
      security.php
      seo.php
    /Support
      Env.php
      Helpers.php
      Html.php
      Csrf.php
      Auth.php
      Pagination.php
      Money.php
      Slug.php
  /storage (utenfor webroot anbefalt)
    /logs
    /sessions
    /cache
    /exports
    /imports
    /mail
  /bin
    cron.php
    migrate.php
    seed.php
    image_jobs.php
  /migrations
  /tests
  composer.json
1.3 Request-livssyklus (server-side)
1.	Apache → /public/index.php
2.	Bootstrap:
o	last .env (kun ved boot, cachet i opcache)
o	init config
o	init DB pool (PDO)
o	init router
3.	Middleware-kjede:
o	SecurityHeaders
o	RateLimit (IP + user)
o	Session
o	Csrf (for muterende)
o	Auth (admin/account)
o	Cache (page cache for GET)
4.	Controller → Service → Repository
5.	Template render (minimal engine) → Response
6.	Logging (structured) + metrics (timings)
________________________________________
2. Apache-konfigurasjon (optimalisering, sikkerhet, rewrite)
2.1 VHost (skisse — prod)
Mål: HTTP/2, gzip/brotli, cache headers, strict TLS, sikre mapper, rene URLer.
Krav:
•	Protocols h2 http/1.1
•	Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
•	Header always set X-Content-Type-Options "nosniff"
•	Header always set X-Frame-Options "DENY"
•	Header always set Referrer-Policy "strict-origin-when-cross-origin"
•	CSP per miljø (report-only i staging → enforce i prod):
o	default-src 'self'
o	img-src 'self' data: https:
o	style-src 'self' 'unsafe-inline' (helst eliminere inline over tid)
o	script-src 'self' (med nonce for inline småskript om nødvendig)
•	Deaktiver directory listing.
•	Block tilgang til:
o	/app, /storage, /migrations, /tests, .env, composer.*
•	AllowOverride None (alt i vhost, ikke .htaccess i prod)
2.2 Rewrite-struktur
Rene URLer:
•	Forside: /
•	Kategori: /kategori/{slug} eller hierarkisk /kategori/{parent}/{child}
•	Produkt: /produkt/{slug} (evt. /p/{id}-{slug} for unikhet og rask lookup)
•	CMS: /side/{slug} eller direkte slug under root for “om-oss”, “kjopsbetingelser” osv.
•	Konto: /konto/...
•	Admin: /admin/...
•	API: /api/...
•	Webhook: /webhooks/stripe
Krav:
•	Alle ikke-eksisterende filer/mapper → index.php
•	301 fra gamle WP/legacy stier (f.eks. /produktkategori/... og query-baserte) til nye canonical.
•	Canonical trailing slash policy (velg én: uten slash anbefalt).
2.3 Statisk caching (assets)
•	Fingerprintede filer: /assets/app.3f2a9c.css og /assets/app.91ab2d.js
o	Cache-Control: public, max-age=31536000, immutable
•	Bilder i /media:
o	thumbnails med fingerprint/hashes eller query signature
o	Cache-Control: public, max-age=604800 (7 dager) + ETag
2.4 Kompresjon
•	Brotli (hvis tilgjengelig) for text/html, text/css, application/javascript, application/json
•	Gzip fallback
2.5 PHP-FPM integrasjon
•	Kjør PHP via FPM (ikke mod_php i prod).
•	ProxyPassMatch til php-fpm socket.
________________________________________
3. PHP backend-arkitektur (MVC-inspirert)
3.1 Routing
•	Egen Router med:
o	GET/POST/PUT/PATCH/DELETE
o	route params ({id}, {slug})
o	named routes
o	middleware per route group
•	Route groups:
o	Public Web
o	Account (auth)
o	Admin (auth + RBAC)
o	API (JSON)
o	Webhooks (rå body + signatur)
3.2 Controllers
Regel: Controller skal være tynn:
•	parse/valider input
•	kalle service/use-case
•	returnere view/json/redirect
3.3 Services/Use-cases
•	Inneholder:
o	prisberegning (inkl. kampanjer)
o	lager (reservering i checkout)
o	ordrestatus og historikk
o	e-post og kvitteringer
o	caching beslutninger (hva er cachebart)
•	Transaksjoner: Alt som endrer ordre/lager skal være i DB-transaksjon.
3.4 Repositories (DB)
•	Kun SQL + mapping til DTO/Models
•	Ingen HTML/format
•	Standard mønster:
o	findById, findBySlug
o	search(filters, pagination)
o	create/update/delete
•	SQL:
o	alltid parametrisert
o	begrens SELECT til nødvendige kolonner
o	bruk indekser (se §5)
3.5 Templatesystem (minimal, ekstremt rask)
Krav:
•	Ren PHP templates + partials
•	Én layout.php med slots:
o	<head> meta, preload, critical CSS
o	header
o	main
o	footer
•	Helper-funksjoner:
o	e($string) HTML-escape
o	url('route.name', [...])
o	asset('app.css') (fingerprint lookup)
o	csrf_field()
•	Komponenter som rene PHP partials:
o	components/product-card.php
o	components/price.php
o	components/breadcrumbs.php
•	Valgfritt: enkel kompilert template-cache (opcache tar mye, men man kan i tillegg cache resolved paths og config).
________________________________________
4. API-lag (PHP REST-endpoints)
4.1 API-prinsipper
•	JSON-only
•	Konsistent envelope:
o	{"ok":true,"data":...,"meta":...}
o	feil: {"ok":false,"error":{"code":"VALIDATION_ERROR","message":"...","fields":{...}}}
•	HTTP status korrekt:
o	200/201/204, 400, 401, 403, 404, 409, 422, 429, 500
•	Rate limiting (IP + session + user)
•	ETag for GET der relevant (f.eks. cart snapshot)
4.2 Public/API (kunde)
•	GET /api/catalog/suggest?q=... (autocomplete; cache 30s)
•	POST /api/cart/items
o	body: {product_id, variant_id?, qty}
•	PATCH /api/cart/items/{line_id}
•	DELETE /api/cart/items/{line_id}
•	GET /api/cart
•	POST /api/checkout/start
o	init shipping + payment intent
•	POST /api/checkout/address
•	POST /api/checkout/shipping-method
•	POST /api/checkout/confirm
o	oppretter ordre, reserverer lager, initierer Stripe Checkout/PaymentIntent
•	GET /api/order/{order_public_id} (kun hvis token eller innlogget)
•	POST /api/account/login
•	POST /api/account/logout
•	POST /api/account/register
•	POST /api/account/password/forgot
•	POST /api/account/password/reset
4.3 Admin/API
•	POST /api/admin/login
•	GET /api/admin/dashboard
•	Produkter:
o	GET /api/admin/products?filters...
o	POST /api/admin/products
o	GET /api/admin/products/{id}
o	PUT /api/admin/products/{id}
o	DELETE /api/admin/products/{id}
•	Kategorier:
o	GET/POST/PUT/DELETE /api/admin/categories...
•	Attributter/varianter:
o	GET/POST/PUT/DELETE /api/admin/attributes...
o	POST /api/admin/products/{id}/variants
•	Ordrer:
o	GET /api/admin/orders?status=...
o	GET /api/admin/orders/{id}
o	POST /api/admin/orders/{id}/status
o	POST /api/admin/orders/{id}/refund (via Stripe)
•	Menyer:
o	GET/PUT /api/admin/menus/{key}
•	CMS-sider:
o	GET/POST/PUT/DELETE /api/admin/pages...
•	E-postmaler:
o	GET/PUT /api/admin/email-templates/{key}
•	Innstillinger:
o	GET/PUT /api/admin/settings
•	Cache:
o	POST /api/admin/cache/purge
o	POST /api/admin/cache/warm?paths[]=...
•	Logger:
o	GET /api/admin/logs?level=...
•	Statistikk:
o	GET /api/admin/stats/orders?from=...&to=...
4.4 Stripe webhooks
•	POST /webhooks/stripe
o	Verifiser signatur (Stripe-Signature)
o	Idempotency: event_id logges og ignoreres ved gjentak
o	Håndter:
	payment_intent.succeeded
	payment_intent.payment_failed
	checkout.session.completed (hvis Checkout)
	charge.refunded
•	All webhook-prosessering:
o	rask ack (200) etter enqueue/registrering hvis mulig (uten kø: prosesser innen 2–3 sek).
________________________________________
5. MySQL database design (3NF, indeksering, ytelseskrav)
5.1 Generelle DB-regler
•	InnoDB overalt
•	UTF8MB4
•	Alle tabeller:
o	id BIGINT UNSIGNED AUTO_INCREMENT (primær)
o	created_at DATETIME(6), updated_at DATETIME(6)
•	Soft delete kun der nødvendig (produkter, brukere, sider) via deleted_at (indeksert).
•	Penger: lagres som int i øre (NOK) eller DECIMAL(10,2); anbefalt: int i øre for fart + konsistens.
•	Slugs: unik per scope (produkt globalt, kategori per parent, side globalt).
•	Store tekstfelt: TEXT og hold dem utenfor “hot” queries (join kun ved behov).
5.2 ER-diagram (tekstlig, omfattende)
5.2.1 Produkter og katalog
•	products
o	id
o	sku (unik, nullable hvis variantstyrt)
o	slug (unik)
o	title
o	subtitle (nullable)
o	brand_id (FK)
o	description_short (TEXT, nullable)
o	description_html (MEDIUMTEXT, nullable) — sanitert
o	weight_grams (INT, nullable)
o	is_active (TINYINT)
o	is_featured (TINYINT)
o	tax_class_id (FK)
o	created_at/updated_at/deleted_at
•	brands
o	id, name (unik), slug
•	categories
o	id
o	parent_id (self FK, nullable)
o	slug (unik per parent)
o	name
o	description_html (TEXT, nullable)
o	sort_order (INT)
o	is_active
•	product_categories (M:N)
o	product_id (FK)
o	category_id (FK)
o	is_primary (TINYINT)
o	PK (product_id, category_id)
o	indeks på (category_id, product_id)
•	product_images
o	id
o	product_id (FK)
o	variant_id (FK nullable)
o	path_original
o	path_webp
o	alt_text
o	sort_order
o	checksum (for dedup)
•	attributes
o	id
o	code (unik, f.eks. size, color)
o	name
o	type ENUM('select','text','number','boolean')
o	is_variant_axis (TINYINT) — om brukes til varianter
•	attribute_values
o	id
o	attribute_id (FK)
o	value (f.eks. “L”, “Rød”)
o	slug (for URL-filter)
o	sort_order
o	unik (attribute_id, value)
•	product_attribute_values (for ikke-variant attributter, M:N)
o	product_id
o	attribute_id
o	attribute_value_id (nullable hvis fritekst)
o	value_text (nullable)
•	product_variants
o	id
o	product_id
o	sku (unik)
o	title (nullable; genereres)
o	price_ore (INT)
o	compare_at_price_ore (INT nullable)
o	is_active
•	variant_attribute_values (hvilke verdier definerer varianten)
o	variant_id
o	attribute_id
o	attribute_value_id
o	unik (variant_id, attribute_id)
o	unik constraint (product_id, kombinasjon) håndheves via app + evt. hash felt:
•	product_variant_signature
o	variant_id
o	signature_hash CHAR(32) (MD5 av “attr:value|…”) unik per product_id
5.2.2 Lager
•	inventory_items
o	id
o	product_id (FK nullable hvis variantbasert)
o	variant_id (FK nullable)
o	stock_on_hand (INT)
o	stock_reserved (INT)
o	reorder_level (INT nullable)
o	warehouse_location (VARCHAR nullable)
o	indeks (variant_id), (product_id)
•	inventory_movements
o	id
o	inventory_item_id
o	type ENUM('in','out','reserve','release','adjust')
o	qty (INT signed)
o	reason (VARCHAR)
o	reference_type (VARCHAR) (order, admin, import)
o	reference_id (BIGINT nullable)
o	created_by_user_id (FK nullable)
o	created_at
5.2.3 Brukere og auth
•	users
o	id
o	email (unik)
o	phone (nullable)
o	password_hash
o	first_name, last_name
o	is_active
o	email_verified_at (nullable)
o	last_login_at (nullable)
o	created_at/updated_at/deleted_at
•	roles
o	id
o	key (unik) (admin, manager, support)
o	name
•	permissions
o	id
o	key (unik) (products.read, products.write, orders.refund, etc.)
•	role_permissions
o	role_id, permission_id (PK)
•	user_roles
o	user_id, role_id (PK)
•	user_addresses
o	id
o	user_id
o	type ENUM('shipping','billing')
o	name
o	address1, address2, postal_code, city, country_code
o	created_at/updated_at
5.2.4 Handlekurv og checkout
•	carts
o	id
o	session_id (unik, nullable hvis user)
o	user_id (nullable)
o	currency (default NOK)
o	created_at/updated_at
•	cart_items
o	id
o	cart_id
o	product_id
o	variant_id (nullable)
o	qty
o	price_ore_snapshot (INT) — pris ved legg-i-kurv
o	title_snapshot (VARCHAR) — for stabilitet
o	created_at/updated_at
o	indeks (cart_id)
•	checkout_sessions
o	id
o	cart_id
o	status ENUM('started','addressed','shipping_selected','payment_started','completed','abandoned')
o	email
o	shipping_address_json (JSON)
o	billing_address_json (JSON)
o	shipping_method_id (FK nullable)
o	totals_json (JSON) — subtotal, shipping, tax, total
o	stripe_payment_intent_id (VARCHAR nullable)
o	expires_at
o	created_at/updated_at
5.2.5 Ordrer
•	orders
o	id
o	public_id (unik, f.eks. ULID)
o	user_id (nullable)
o	email
o	status ENUM('pending','paid','processing','shipped','completed','cancelled','refunded')
o	payment_status ENUM('unpaid','authorized','paid','refunded','failed')
o	fulfillment_status ENUM('unfulfilled','partial','fulfilled')
o	currency
o	subtotal_ore, shipping_ore, tax_ore, discount_ore, total_ore
o	shipping_address_json (JSON)
o	billing_address_json (JSON)
o	shipping_method_snapshot (JSON)
o	notes (TEXT nullable)
o	created_at/updated_at
o	indeks (status, created_at), (user_id, created_at), (public_id)
•	order_items
o	id
o	order_id
o	product_id
o	variant_id (nullable)
o	sku_snapshot
o	title_snapshot
o	unit_price_ore
o	qty
o	line_total_ore
o	created_at
o	indeks (order_id)
•	order_payments
o	id
o	order_id
o	provider ENUM('stripe')
o	provider_payment_intent_id
o	status ENUM('created','authorized','captured','failed','refunded')
o	amount_ore
o	raw_json (JSON)
o	created_at
•	order_status_history
o	id
o	order_id
o	from_status
o	to_status
o	comment (VARCHAR nullable)
o	created_by_user_id (nullable)
o	created_at
5.2.6 Menyer og CMS
•	menus
o	id
o	key (unik) (header_main, footer_1, footer_2)
o	name
•	menu_items
o	id
o	menu_id
o	parent_id (self FK nullable)
o	type ENUM('category','product','page','url')
o	ref_id (BIGINT nullable)
o	url (VARCHAR nullable)
o	label
o	sort_order
o	is_active
•	pages
o	id
o	slug (unik) (om-oss, kjopsbetingelser, angrerett-retur, personvern)
o	title
o	meta_title
o	meta_description
o	content_html (MEDIUMTEXT) — sanitert
o	is_active
o	updated_by_user_id
o	created_at/updated_at/deleted_at
5.2.7 Innstillinger, frakt, e-post
•	settings
o	id
o	key (unik) (shipping.free_over_ore, stripe.public_key, etc.)
o	value (TEXT) (evt. JSON)
o	updated_at
•	shipping_methods
o	id
o	code (unik)
o	name
o	price_ore
o	free_over_ore (nullable)
o	is_active
o	sort_order
•	email_templates
o	id
o	key (unik) (order_confirmation, shipping_confirmation, password_reset)
o	subject
o	body_html
o	body_text (nullable)
o	updated_at
5.2.8 Logg og audit
•	logs
o	id
o	level ENUM('debug','info','warning','error','security')
o	message
o	context_json (JSON)
o	request_id
o	user_id (nullable)
o	ip
o	user_agent
o	created_at
o	indeks (level, created_at), (request_id)
•	audit_logs
o	id
o	actor_user_id
o	action (VARCHAR) (product.update, order.refund)
o	entity_type (VARCHAR)
o	entity_id (BIGINT)
o	diff_json (JSON) — før/etter
o	ip, user_agent
o	created_at
5.3 Indekseringskrav (minimum)
•	products.slug UNIQUE
•	products.is_active index (sammen med id eller created_at)
•	categories(parent_id, slug) UNIQUE
•	product_categories(category_id, product_id) index
•	product_variants(product_id, is_active) index
•	inventory_items(variant_id) index, inventory_items(product_id) index
•	orders(public_id) UNIQUE + index
•	orders(status, created_at) index
•	order_items(order_id) index
•	users(email) UNIQUE
•	pages(slug) UNIQUE
•	menu_items(menu_id, parent_id, sort_order) index
5.4 Query-strategier (ytelse)
•	Kategori-listing:
o	1 query: produkt-IDs via join product_categories + filters
o	1 query: hent produktkort-data for disse IDs (IN-liste, begrenset)
o	1 query: hent primærbilde (via subquery eller precomputed products.primary_image_id)
•	Produkt-side:
o	1 query: produkt + brand
o	1 query: bilder
o	1 query: varianter + variant-attributter
o	1 query: lager for varianter (batch)
o	1 query: breadcrumbs (kategori-sti cachet)
•	Bruk precomputed felt der det kutter joins:
o	products.primary_category_id
o	products.primary_image_id
o	products.price_from_ore, products.price_to_ore (for variantspenn)
________________________________________
6. Ytelse: caching, headers, ressursstrategi (gjennomgående)
6.1 Cache-lag (obligatorisk)
1.	OPcache (PHP):
o	opcache.enable=1
o	opcache.validate_timestamps=0 i prod (deploy invalidates)
o	opcache.max_accelerated_files høyt nok
2.	APCu:
o	hot data: settings, menytre, category tree, feature flags
3.	Filcache (storage/cache):
o	Page cache for offentlige GET-sider (forside/kategori/produkt/cms)
o	Fragment cache: header/footer/meny/breadcrumbs
4.	HTTP cache (browser/CDN om mulig):
o	immutable assets
o	kort TTL på HTML + stale-while-revalidate (dersom CDN brukes)
6.2 Page cache-regler
•	Cachebar:
o	/
o	/kategori/*
o	/produkt/*
o	/side/*
•	Ikke cachebar:
o	/kasse*, /handlekurv* (kan delvis cache skeleton)
o	/konto*
o	/admin*
•	Vary:
o	språk (hvis flerspråk)
o	valuta (om relevant)
o	cookie: unngå vary på cookies for public sider (hold dem cookie-frie)
•	Invalidering:
o	Produkt oppdatert → purge produkt-URL + relevante kategorier + forside “utvalgte/populære”
o	Kategori oppdatert → purge kategori-URL + barn
o	CMS-side oppdatert → purge side-URL
•	Cache key:
o	path + query canonical (sorter parametre)
o	user-agent ignoreres (ikke vary)
6.3 HTML/Head optimalisering
•	Critical CSS inline (minimalt) for above-the-fold (header + hero + produktkort)
•	Resten som preload + async:
o	<link rel="preload" as="style" href="/assets/app.hash.css" onload="this.rel='stylesheet'">
•	JS:
o	Del opp i:
	core (små helpers)
	catalog (filters/pagination)
	product (variantvelger)
	cart (mini-cart)
	checkout (kun i checkout)
o	defer alltid, ingen render-blocking JS
•	Fonts:
o	Systemfont-stack default; hvis brand-font: self-host WOFF2 + preload + font-display: swap
6.4 Bildeoptimalisering
•	Master i høy kvalitet, generer:
o	WebP (primær)
o	fallback JPG/PNG ved behov
•	Responsive srcset + sizes
•	Lazyload alle ikke-hero bilder (loading="lazy")
•	LCP-bilde:
o	preload (<link rel="preload" as="image" href="...">)
o	ingen lazy på LCP
•	Thumbnails pipeline:
o	lagre i /media/cache/{w}x{h}/{hash}.webp
o	cache i filsystem + header caching
6.5 MySQL-optimalisering (konkret praksis)
•	EXPLAIN ANALYZE på alle kritiske queries:
o	kategori listing
o	produkt + varianter
o	checkout create order
•	Unngå SELECT *
•	Unngå N+1:
o	batch-hent images/variants/inventory
•	Bruk covering indexes for listing queries
•	Store “tunge” tekster separat og ikke join i listing
________________________________________
7. Frontend-spec — globale komponenter
7.1 Design system (komponentbibliotek)
Mål: Premium, ryddig, høy tillit, salgsorientert, bedre navigasjon enn dagens (mye kategoritekst og lange lister). 
Tokens (CSS variabler)
•	Spacing: 4/8/12/16/24/32/48/64
•	Radius: 8/12/16
•	Shadow: 1–3 nivåer
•	Typografi:
o	H1 32–40
o	H2 24–32
o	Body 16–18
o	Small 13–14
•	Farger:
o	Base: hvit/lys bakgrunn
o	Tekst: nesten-svart
o	Accent: brand-farge (defineres etter visuell analyse; implementer som CSS var)
o	Status: grønn (på lager), gul (få), rød (tom)
•	Grid:
o	max-width container: 1200–1320
o	12-kolonne grid desktop, 4–6 kolonne mobil
Komponenter (obligatorisk)
•	Header:
o	Logo
o	Søk (typeahead)
o	Kategorinav (mega-meny)
o	Konto
o	Handlekurv (mini)
•	Product card:
o	bilde, tittel, pris, evt. førpris, lagerindikator
o	“Legg i handlekurv” (hurtig) der variant ikke kreves
•	Filters:
o	attributtfiltre + pris + sortering
•	Breadcrumbs
•	Pagination (server-side)
•	Trustbar:
o	fri frakt over X (i dag kommuniseres “fri frakt over 2000,-” på siden) 
o	leveringstid
o	kontaktinfo
•	Footer:
o	snarveier + nyttig informasjon (i dag vises Om, Kjøpsbetingelser, Angrerett/retur, Personvern) 
7.2 Interaksjonsprinsipper (microinteractions)
•	Knapper:
o	hover: subtil lift/shadow
o	active: trykk
o	loading: spinner + disable
•	Formfelt:
o	inline validering
o	klare feilmeldinger
•	Mini-cart:
o	slide-in panel, focus trap, ESC-lukk
o	oppdatering uten full reload
•	Variantvelger:
o	tilgjengelighet: disable utilgjengelige kombinasjoner
•	Toasts:
o	“Lagt i handlekurv”, “Oppdatert antall”, “Feil: …”
7.3 SEO og semantikk (globalt)
•	Semantisk HTML5:
o	<header> <nav> <main> <footer>
•	Structured data:
o	Product schema (produkt)
o	BreadcrumbList
o	Organization
•	Meta:
o	canonical
o	meta title/description fra DB
•	Open Graph + Twitter cards
•	Robots + sitemap.xml (generert)
•	301-mapping fra gamle URLer
________________________________________
8. Frontend-spec — sidemaler (ekstremt detaljert)
For hver mal: funksjonelle krav, UX, datakilder, struktur, responsiv, microinteractions, SEO, ytelse.
________________________________________
8.1 Header (global partial)
Funksjonelle krav
•	Sticky på scroll (desktop), men med “smart shrink” (høyde reduseres etter 64px scroll).
•	Logo → hjem.
•	Primærnavigasjon:
o	Mega-meny for hovedkategorier (Scooter/Moped, Lett MC/MC, ATV, Hage, Dekk, Utstyr, etc.) — tilsvarer dagens toppområder. 
•	Søkefelt:
o	typeahead etter 2 tegn
o	returnerer produkter + kategorier + evt. populære søk
•	Ikoner:
o	Konto (login/my account)
o	Handlekurv med badge (antall)
•	Trustbar (valgfri stripe over header):
o	“Fri frakt over X” + levering + kundeservice
UX-krav
•	Tastatur:
o	Tab-navigasjon hele menyen
o	ESC lukker mega-meny og mini-cart
•	Mobil:
o	hamburger åpner fullskjerms nav-panel med søk øverst
•	Tilgjengelighet:
o	aria-labels, aria-expanded
Datakilder
•	menus, menu_items for header
•	settings for frakt-tekst, kontakt
•	Typeahead: products + categories via /api/catalog/suggest
Ytelse
•	Header fragment-cache (APCu + filcache) invalideres ved menyendring.
________________________________________
8.2 Footer (global partial)
Funksjonelle krav
•	3 kolonner desktop:
o	Kontakt (epost, telefon)
o	Snarveier (kategorier)
o	Nyttig info (konto, om, kjøpsbetingelser, angrerett, personvern)
•	Bottom bar:
o	org-info + copyright
o	“Utviklet av …” kan beholdes som kreditering i admin-innstilling (valgfritt) — dagens footer viser utviklerkreditt. 
Datakilder
•	menus key: footer_1, footer_2
•	settings for kontaktfelt
•	pages for policy-lenker
Ytelse
•	100% cachebar fragment.
________________________________________
8.3 Forside (/)
Funksjonelle krav
•	Hero-seksjon:
o	1–2 kampanjekort (CMS-styrt)
•	Kategori-kort grid (6 hovedkategorier)
•	“Utvalgte produkter” (manuelt kuratert via admin)
•	“Populære produkter” (basert på salg siste 30 dager + fallback)
•	Trust-seksjon (frakt, retur, kundeservice)
•	Nyhets/guide-blokk (valgfri CMS)
UX-krav
•	Above-the-fold:
o	hero + søk synlig innen 600px høyde
•	Produktkort:
o	tydelig pris og evt. førpris “SALG”
o	vis lagerstatus (på lager / få / bestillingsvare)
•	CTR-optimalisering:
o	store klikkflater, tydelig hierarki
Innholdskilder
•	pages (home blocks) eller egen tabell home_sections (anbefalt)
•	products (featured)
•	orders aggregat for popular
Designstruktur
•	Seksjoner:
1.	Hero
2.	Category grid
3.	Featured carousel (ikke tung; bruk CSS scroll-snap, ikke JS slider)
4.	Popular grid
5.	Trustbar
6.	CMS
Responsiv
•	Mobil:
o	1 kol hero, category grid 2 kol, produkter 2 kol
•	Desktop:
o	category grid 3 kol, produkter 4 kol
Microinteractions
•	“Legg i handlekurv” quick-add ved enkle produkter
•	Favoritt (valgfritt senere; ikke i scope med mindre ønsket)
SEO
•	H1: “Delebestilling på nett” / brand
•	Interne lenker til hovedkategorier
Ytelse
•	Page cache ON
•	Minimal JS på forside (kun mini-cart og søk)
________________________________________
8.4 Om oss (/om-oss)
Funksjonelle krav
•	Ren CMS-side
•	Mulighet for å vise:
o	historie
o	lager/butikk info
o	kontakt
o	åpningstider (valgfritt)
•	CTA til kategorier
Datakilder
•	pages slug om-oss
•	settings kontakt
SEO
•	meta fra pages
Ytelse
•	100% cachebar
________________________________________
8.5 Produktarkiv/kategorisider (/kategori/{slug...})
Funksjonelle krav
•	Breadcrumbs
•	Kategoritittel + kort intro (collapsible)
•	Subkategorier som chips/kort
•	Produktlisting:
o	server-side pagination
o	sortering: relevans, pris asc/desc, mest solgt, nyeste
•	Filtrering:
o	attributter (select)
o	pris range (enkelt: min/max)
o	“på lager” toggle
•	“Antall produkter” og “viser X–Y”
•	Tom-tilstand: forslag til andre kategorier
Datakilder
•	categories
•	product_categories
•	products + product_variants (for pris)
•	inventory_items (lagerfilter)
•	attributes + mapping-tabeller
Designstruktur
•	Desktop:
o	venstre filterkolonne (sticky)
o	høyre produktgrid
•	Mobil:
o	filter i bottom sheet/modal
Responsiv atferd
•	Filter modal med:
o	apply/cancel
o	viser antall treff før apply (krever API eller server count)
o	enkel: server apply med query params
Microinteractions
•	Filter chips med “x”
•	“Vis mer” i kategori-intro
SEO
•	Canonical for første side uten page=1
•	rel=prev/next (valgfritt)
•	Indexering:
o	Hovedkategori: index
o	Tunge filterkombinasjoner: noindex (policy definert i seo.php)
•	Breadcrumb structured data
Ytelse
•	Kategori-side page-cache ON (for canonical uten brukerstate)
•	Filterkombinasjoner kan caches med kort TTL (60–300s)
•	DB: maks 12 queries (se §0.1)
________________________________________
8.6 Produktside (/produkt/{slug})
Funksjonelle krav
•	Galleri:
o	1 hovedbilde + thumbs
o	zoom (CSS transform, minimal JS)
•	Produktinfo:
o	tittel
o	SKU (variant)
o	pris
o	førpris hvis tilbud
o	lagerstatus
o	fraktinfo (fra settings)
•	Variantvelger:
o	for akser (størrelse/farge osv.)
o	dynamisk oppdatere pris/lager
•	CTA:
o	“Legg i handlekurv”
o	qty stepper
•	Tabs/accordions:
o	Beskrivelse
o	Spesifikasjoner (attributter)
o	Frakt/retur
•	Relaterte produkter:
o	samme kategori/brand, evt. “kunder kjøpte også”
Datakilder
•	products, brands
•	product_images
•	product_variants, variant_attribute_values, attribute_values
•	inventory_items
•	settings (frakt/retur info)
•	product_attribute_values (spesifikasjoner)
•	product_categories (relaterte)
Designstruktur
•	Desktop: 2 kol (galleri / info)
•	Mobil: galleri over, sticky add-to-cart bar nederst etter scroll
Microinteractions
•	Variant endring:
o	disable utilgjengelige valg
o	oppdater pris/lager uten reload (API GET variant snapshot)
•	Add-to-cart:
o	spinner på knapp
o	mini-cart åpnes
SEO
•	H1 = produktnavn
•	Product schema: pris, lager, brand, bilder
•	canonical
•	unike meta fra produkt (fallback: title + kategori)
Ytelse
•	Cachebar HTML (public) men variant-pris kan være:
o	pre-render default variant i HTML
o	JS henter snapshot ved endring
•	Bilder: preload første bilde
•	Maks 90 KB gz HTML
________________________________________
8.7 Min konto (/konto)
Sider
•	/konto/ordre (liste)
•	/konto/ordre/{public_id} (detalj)
•	/konto/profil (endre navn, passord)
•	/konto/adresser (valgfritt)
Funksjonelle krav
•	Krever login
•	Ordreoversikt:
o	status, dato, total, CTA “Vis”
•	Ordredetalj:
o	ordrelinjer, leveringsadresse, frakt, betaling
•	Profil:
o	endre navn, telefon
o	endre passord (krever nåværende)
•	Sikkerhet:
o	sessions oversikt (valgfritt)
Datakilder
•	users, orders, order_items, order_payments
UX
•	Tydelig tom-tilstand (ingen ordre)
•	Feilmeldinger uten å lekke info
Ytelse
•	Ikke page-cache (personlig data)
•	DB: optimaliser med indeks på (user_id, created_at)
________________________________________
8.8 Handlekurv (/handlekurv)
Funksjonelle krav
•	Liste linjer:
o	produktnavn, variant, pris, qty stepper, linjesum, fjern
•	Totals:
o	subtotal, frakt-estimat (basert på settings eller postnummer)
o	CTA “Til kassen”
•	Cross-sell (valgfritt)
Datakilder
•	carts, cart_items
•	produktsnapshots brukes primært, men valider ved rendering:
o	sjekk at produkter fortsatt aktive, pris oppdateres ved checkout
Microinteractions
•	qty endring uten reload
•	fjern med undo (toast)
Ytelse
•	Minimal JS
•	Ikke page-cache (varierer per session)
________________________________________
8.9 Kasse (/kasse)
Funksjonelle krav
•	Steg:
1.	Kundeinfo (epost, telefon)
2.	Levering (adresse)
3.	Fraktmetode
4.	Betaling (Stripe)
5.	Bekreftelse
•	Validering:
o	postnummerformat
o	påkrevd felter
•	Lagerkontroll:
o	før betaling: reserver lager
o	ved betaling success: konverter reservering til trekk
Datakilder
•	checkout_sessions, carts, cart_items, shipping_methods, settings
•	Stripe tokens/ids i checkout_sessions og order_payments
UX
•	Autofyll, tydelige stegindikatorer
•	“Tilbake” uten tap av data
•	Feil i betaling → vennlig, og mulighet for retry
Sikkerhet
•	CSRF på alle steg
•	Rate limit på start checkout
Ytelse
•	Ikke page-cache
•	JS kun i checkout bundle
________________________________________
8.10 Kjøpsbetingelser (/kjopsbetingelser)
CMS-side fra pages. Cachebar.
8.11 Angrerett og retur (/angrerett-retur)
CMS-side fra pages. Cachebar.
8.12 Personvernerklæring (/personvern)
CMS-side fra pages. Cachebar.
________________________________________
9. Backend / Adminpanel SPEC (PHP + MySQL)
9.1 Admin grunnkrav (globalt)
•	URL: /admin
•	Login:
o	epost + passord
o	2FA TOTP (valgfritt men spesifisert som “skal støtte”)
•	Session:
o	kort idle timeout (30 min)
o	absolute timeout (12 timer)
•	RBAC:
o	Roller: Admin, Manager, Support, Content
•	Audit logging på alle mutasjoner:
o	hvem, hva, før/etter, IP
Admin UI
•	Lett og rask:
o	server-rendered sider med små JS for tabeller/modals
o	søk/filter/paginering server-side
________________________________________
9.2 Modul: Dashboard
Funksjoner
•	KPI kort:
o	Omsetning i dag / siste 7 / siste 30
o	Antall ordre
o	Konvertering (hvis sessions spores)
o	Top produkter (7 dager)
o	Lav lager (under reorder_level)
•	“Siste ordre” liste
Validering/sikkerhet
•	Kun read for de fleste roller, write ingen
API
•	GET /api/admin/dashboard
DB
•	Aggregater fra orders, order_items, inventory_items
UX
•	Rask last, skeleton
________________________________________
9.3 Modul: Produktadministrasjon (CRUD)
9.3.1 Produkter
Funksjoner
•	Liste:
o	søk (tittel, sku)
o	filter: aktiv, kategori, brand, lagerstatus
•	Opprett:
o	grunninfo + slug auto-generering
o	velg kategorier (en primær)
o	pris:
	enkel pris eller variantstyrt
•	Rediger:
o	beskrivelse (HTML editor med sanitization)
o	bilder (upload, rekkefølge, alt-tekst)
o	attributter (spesifikasjoner)
o	varianter (hvis aktivert)
o	SEO: meta title/desc, canonical override (valgfritt)
•	Slett:
o	soft delete
•	Bulk:
o	aktiver/deaktiver
o	prisjustering (prosent)
o	flytt kategori
Validering
•	slug unik
•	pris >= 0
•	SKU unik per variant
•	bilder: mime-type whitelist, max size, virus-scan valgfritt
Sikkerhet
•	RBAC: products.write
•	CSRF på alle actions
•	XSS: sanitization på HTML felt
API endpoints
•	GET/POST/PUT/DELETE /api/admin/products...
•	POST /api/admin/products/{id}/images
•	POST /api/admin/products/{id}/variants
•	POST /api/admin/products/{id}/attributes
DB tabeller
•	products, product_images, product_categories,
product_variants, variant_attribute_values,
product_attribute_values
UX
•	Rediger-skjerm med tabs:
1.	Grunninfo
2.	Pris/varianter
3.	Bilder
4.	Spesifikasjoner
5.	SEO
9.3.2 Kategorier (CRUD)
Funksjoner
•	Trevisning (drag/drop sort_order)
•	Rediger:
o	navn, slug, parent
o	intro-tekst
o	SEO
•	Slett:
o	blokker slett hvis har produkter (tilby flytt)
API
•	GET/POST/PUT/DELETE /api/admin/categories
DB
•	categories, product_categories
9.3.3 Attributter (CRUD)
Funksjoner
•	Lag attributt:
o	type select/text/number/boolean
o	markér som variantakse
•	Verdier:
o	opprett/endre/slett verdier
•	Knytning:
o	i produkt: velg hvilke attributter som brukes
API
•	GET/POST/PUT/DELETE /api/admin/attributes
•	GET/POST/PUT/DELETE /api/admin/attribute-values
DB
•	attributes, attribute_values, mapping-tabeller
________________________________________
9.4 Modul: Ordreadministrasjon
Funksjoner
•	Liste:
o	filter status, dato, epost, ordre-ID
•	Detalj:
o	ordrelinjer
o	betaling (Stripe id)
o	adresse
o	status-historikk
•	Statusendring:
o	pending → paid → processing → shipped → completed
o	cancel/refund (Stripe)
•	Refusjon:
o	hel/delvis (delvis krever line-level, kan implementeres fase 2)
•	Eksport:
o	CSV for regnskap (valgfritt)
Validering
•	Statusmaskin: kun gyldige overganger
•	Refund <= betalt beløp
Sikkerhet
•	orders.write, orders.refund egne permissions
•	audit log på refund
API
•	GET /api/admin/orders
•	GET /api/admin/orders/{id}
•	POST /api/admin/orders/{id}/status
•	POST /api/admin/orders/{id}/refund
DB
•	orders, order_items, order_payments, order_status_history
UX
•	Tydelig “betalingsstatus” og “fulfillmentstatus”
•	Hurtigknapper for status
________________________________________
9.5 Modul: Menybygger
Funksjoner
•	Rediger header/footer menyer
•	Drag/drop hierarki
•	Elementtyper:
o	kategori, side, produkt, url
Sikkerhet
•	menus.write
API
•	GET/PUT /api/admin/menus/{key}
DB
•	menus, menu_items
________________________________________
9.6 Modul: CMS-sider
Funksjoner
•	CRUD pages
•	WYSIWYG/HTML editor (sanitization)
•	SEO felter
•	Preview URL
Sikkerhet
•	pages.write
API
•	GET/POST/PUT/DELETE /api/admin/pages
DB
•	pages
________________________________________
9.7 Modul: E-postmaler
Funksjoner
•	Rediger subject + HTML + tekst
•	Testsend (til admin epost)
Sikkerhet
•	email_templates.write
API
•	GET/PUT /api/admin/email-templates/{key}
DB
•	email_templates
________________________________________
9.8 Stripe-integrasjon (betaling + webhooks)
Betalingsflyt (anbefalt)
•	Checkout oppretter PaymentIntent eller Checkout Session
•	Kunde betaler
•	Webhook bekrefter betaling
•	Ordre settes paid
Krav
•	Stripe keys i settings/env
•	Webhook secret i env
•	Idempotent prosessering
•	Lagre raw webhook payload i order_payments.raw_json eller egen stripe_events
DB
•	order_payments + evt. stripe_events(event_id UNIQUE, payload_json, created_at)
________________________________________
9.9 Fraktinnstillinger
Funksjoner
•	CRUD shipping methods
•	fri frakt terskel (f.eks. 2000,- som kommuniseres i dag) 
•	beregningsregel:
o	standard fastpris
o	gratis over terskel
o	(fase 2) vekt/soner
API
•	GET/PUT /api/admin/settings (for terskel)
•	GET/POST/PUT/DELETE /api/admin/shipping-methods
DB
•	shipping_methods, settings
________________________________________
9.10 Brukeradministrasjon
Funksjoner
•	Liste/search brukere
•	Se ordre per bruker
•	Deaktiver bruker
•	Reset passord-lenke
Sikkerhet
•	users.write begrenset til admin
API
•	GET/POST/PUT /api/admin/users...
DB
•	users, user_addresses
________________________________________
9.11 Cache-control-seksjon
Funksjoner
•	Vis cache status:
o	antall filer
o	størrelse
o	sist purget
•	Purge:
o	hele cache
o	per URL
•	Warm:
o	forside + top kategorier + top produkter
Sikkerhet
•	cache.write
API
•	POST /api/admin/cache/purge
•	POST /api/admin/cache/warm
DB
•	ingen (valgfritt logg i audit_logs)
________________________________________
9.12 Loggsystem
Funksjoner
•	Viewer:
o	filter nivå, dato, request_id, user_id
•	Sikkerhetslogg egen tab
API
•	GET /api/admin/logs
DB
•	logs, audit_logs
________________________________________
9.13 Statistikkvisning
Funksjoner
•	Graf: ordre per dag (30/90/365)
•	Top produkter
•	Refusjoner
•	Lager: lav lager liste
API
•	GET /api/admin/stats/orders
•	GET /api/admin/stats/products
DB
•	orders, order_items, inventory_items
________________________________________
10. Designretning basert på dagens motorleaks.no (konkret moderniseringsplan)
10.1 Observasjoner fra dagens side (som må forbedres)
•	Tydelig kategorifokus og mange lange kategoritekster (“Vis mer”) som kan skygge for produktene. 
•	Forsiden har “Utvalgte produkter” og flere salgsblokker, men presentasjonen kan strømlinjeformes for premium-følelse. 
•	Navigasjon virker stor/omfattende med mange underkategorier (merker/modeller). 
•	Footer har nyttige policy-lenker og kontakt; behold men gjør mer strukturert. 
10.2 Målbilde (premium, troverdig, profesjonell)
Struktur
•	Søk som primær inngang (store deler, mange SKUer):
o	prominent søk i header + “Finn deler til ditt kjøretøy” som guided browse
•	Mega-meny med:
o	toppkategorier
o	populære merker
o	snarveier til “Dekk”, “Hjelmer”, “Olje” (dette finnes i dagens innholdsfokus) 
UI/typografi
•	Ryddig whitespace, tydelig hierarki
•	Produktkort med:
o	tydelig pris, evt. førpris
o	“På lager” indikator
o	rask add-to-cart når mulig
Konverteringsdesign
•	Sticky add-to-cart på mobil produktside
•	Trustbar: frakt, levering, retur, kundeservice
•	Checkout: færre felt, bedre autofyll, tydelig feilhåndtering
Komponentbibliotek
•	Definer alle komponenter som gjenbrukbare partials + BEM/utility classes
•	Storybook er ikke ønsket (tungt), men lag en intern “UI kitchen sink” side i admin (kun dev) som viser komponenter.
________________________________________
11. Filstruktur og build pipeline (uten tunge rammeverk)
11.1 CSS
•	Enten:
o	BEM med egen minimal utility-sett (anbefalt)
o	eller “utility-first light” (egen) uten Tailwind runtime
•	Bygg:
o	SCSS optional, men kan holdes ren CSS med variabler
•	Output:
o	app.hash.css
11.2 JS
•	ES modules
•	Bygg med ekstremt lett bundler (esbuild anbefalt som build-verktøy, ikke runtime-framework)
•	Output:
o	core.hash.js
o	catalog.hash.js
o	product.hash.js
o	checkout.hash.js
11.3 HTML
•	Server-rendered
•	Progressive enhancement (JS forbedrer, men baseline fungerer uten JS)
________________________________________
12. Sikkerhetsspesifikasjoner (detaljert)
12.1 SQL injection
•	PDO prepared statements overalt
•	For IN (...):
o	generer placeholders dynamisk
o	aldri string-concat brukerinput
12.2 XSS
•	Standard: escape ALT
•	Tillat HTML i CMS/prod-beskrivelse:
o	sanitization whitelist:
	p, br, ul, ol, li, strong, em, a (href), h2/h3, table (valgfritt)
o	strip script/style/on* attributes
•	CSP i prod for ekstra beskyttelse
12.3 CSRF
•	Token per session
•	Token roteres ved login
•	Hidden input i forms + header for fetch requests
12.4 Auth
•	Password policy:
o	min 12 tegn
o	rate-limit login attempts (per IP + email)
•	Account enumeration protection:
o	“Hvis epost finnes, sendes lenke” på forgot-password
12.5 Admin hardening
•	/admin bak:
o	obligatorisk 2FA for Admin (kan håndheves)
o	IP allowlist (valgfritt)
•	Audit log for alle admin-endringer
________________________________________
13. Migrering fra dagens løsning (praktisk, uten WP-avhengighet)
13.1 Data-import
•	Import scripts i /bin/imports
•	Støtt:
o	produkter
o	kategorier
o	bilder
o	kunder (valgfritt)
o	ordre (valgfritt, for historikk)
•	Mapping:
o	gamle kategori-URLer til nye slugs
•	Etter import:
o	bygg søkeindeks (hvis egen)
o	generer thumbnails batch
13.2 URL-redirects
•	301 fra:
o	/produktkategori/... til /kategori/... 
o	gamle produkt-URLer til nye /produkt/...
•	Lag redirect-tabell:
o	redirects(old_path UNIQUE, new_path, status_code, hits, created_at)
________________________________________
14. Eksplicitte leveransekrav (Cursor-klar filgenerering)
14.1 Minimum leveranse (MVP, produksjonsklar)
•	Public:
o	forside
o	kategori
o	produkt
o	handlekurv
o	checkout (Stripe)
o	konto (ordreoversikt + detalj)
o	CMS/policy sider
•	Admin:
o	login + RBAC
o	produkter/kategorier/attributter
o	ordre
o	meny
o	CMS
o	fraktinnstillinger
o	cache purge
o	logs
14.2 Kvalitetskrav
•	Unit tests for:
o	pricing
o	inventory reserve/release
o	order state transitions
•	Integrasjonstester:
o	checkout happy path
o	stripe webhook idempotency
•	Observability:
o	request_id i alle logs
o	timings: total, db, render

