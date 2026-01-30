# Deploy til motorleaks.illeris-web.no

## 1. GitHub Secrets

I repo: **Settings → Secrets and variables → Actions** må disse finnes:

| Secret        | Eksempel / verdi |
|---------------|-------------------|
| `FTP_SERVER`  | `ftp.motorleaks.illeris-web.no` |
| `FTP_USERNAME`| `illeris@motorleaks.illeris-web.no` |
| `FTP_PASSWORD`| (FTP-passordet du har satt) |

Legg til `FTP_SERVER` og `FTP_USERNAME` hvis du kun har lagt inn `FTP_PASSWORD` fra før.

---

## 2. Database i phpMyAdmin

1. Logg inn på phpMyAdmin for serveren.
2. Velg databasen **motorlaaksilleri_illeris** (eller opprett den først).
3. Gå til fanen **SQL**.
4. Åpne filen **`database/schema_motorleaks.sql`** i prosjektet, kopier alt innhold og lim inn i SQL-feltet.
5. Klikk **Kjør**. Da opprettes alle tabeller.

Deretter kan du kjøre seed manuelt (admin/kunde-brukere osv.) eller legge inn brukere direkte i phpMyAdmin.

---

## 3. .env på serveren (etter første FTP-deploy) – viktig

FTP-deploy laster **ikke** opp `.env` (den er ekskludert). Uten `.env` bruker appen standardverdier (f.eks. bruker `root` uten passord) og du får **«Access denied»** eller **PDOException**.

**Gjør dette:**

1. På serveren: gå til mappen **`public_html`** (samme mappe som `app/`, `public/`, `vendor/`).
2. Opprett en fil som heter **`.env`** (med punktum foran). Bruk FTP eller filmanager i hosting-panelet.
3. Sett inn innhold som under. **Bytt ut `DB_PASSWORD`** med det faktiske database-passordet.

```env
APP_ENV=production
APP_DEBUG=0
APP_URL=https://motorleaks.illeris-web.no

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=motorlaaksilleri_illeris
DB_USERNAME=motorlaaksilleri_illeris
DB_PASSWORD=ditt_ekte_db_passord_her
DB_CHARSET=utf8mb4

# Nødvendig når document root er public_html (rot): CSS og JS må lastes fra /public/assets/
ASSET_BASE_PATH=/public

SESSION_LIFETIME=120

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

MAIL_FROM=noreply@motorleaks.illeris-web.no
```

**Sjekk:** Filen skal ligge som `public_html/.env` (ikke inne i `public/`). Etter at `.env` er på plass, last inn siden på nytt.

---

## 4. Document root (web root) og 403 Forbidden

Filer deployes til **`/public_html`** på serveren.

**Hvis du får «Forbidden» (403):**

- **Variant A (anbefalt):** Sett document root for domenet til **`public_html/public`** i hosting-panelet. Da brukes `public/index.php` og `public/.htaccess` direkte.
- **Variant B:** La document root være **`public_html`**. Da brukes **`index.php`** og **`.htaccess`** i roten av deploy (repo). De sender alle forespørsler til appen. Sørg for at disse filene er lastet opp (de ligger i repo og inkluderes i deploy).

Begge varianter fungerer. Variant A er ryddigst.

---

## 5. Skrivbare mapper

Sørg for at disse mappene finnes og er skrivbare for PHP (chmod 755 eller 775, eier som webserver):

- `storage/logs/`
- `storage/sessions/`
- `storage/cache/`
- `public/uploads/products/`

---

## 6. Deploy

- **Automatisk:** Push til `main` → GitHub Actions kjører deploy-workflowen og laster opp via FTP.
- **Manuelt:** Repo → **Actions** → **Deploy til server (FTP)** → **Run workflow**.

Etter første deploy: opprett `.env`, importer SQL, sett document root og rettigheter som over. Deretter vil senere push til `main` oppdatere filene uten å overskrive `.env`.
