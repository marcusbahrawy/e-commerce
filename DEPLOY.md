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

## 3. .env på serveren (etter første FTP-deploy)

FTP-deploy laster **ikke** opp `.env` (den er ekskludert). Du må opprette `.env` manuelt på serveren, f.eks. via FTP eller filmanager i hosting-panelet.

Minimal innhold (tilpass passord/host om nødvendig):

```env
APP_ENV=production
APP_DEBUG=0
APP_URL=https://motorleaks.illeris-web.no

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=motorlaaksilleri_illeris
DB_USERNAME=motorlaaksilleri_illeris
DB_PASSWORD=<ditt-db-passord>
DB_CHARSET=utf8mb4

SESSION_LIFETIME=120

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

MAIL_FROM=noreply@motorleaks.illeris-web.no
```

**Viktig:** Endre `DB_PASSWORD` hvis du bruker et annet passord, og fyll inn Stripe-nøkler når betaling skal brukes.

---

## 4. Document root (web root)

Appen må kjøres med **document root** satt til mappen **`public`** (der `index.php` ligger).

- Hvis du kan velge document root for domenet (f.eks. i cPanel / Illeris panel): sett den til mappen **`public`** innenfor FTP-rot (f.eks. `public_html/public` eller `motorleaks/public`).
- Hvis document root **må** være FTP-rot: da må du flytte innholdet i `public/` til roten og endre stier i `index.php` til app-mappen – da bør du heller få dokument root satt til `public`.

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
