# Real Estate Admin (Slim 4)

SuperAdmin authentication, manual property CRUD, and external API property sync with MySQL, Tailwind CSS, and jQuery.

## Requirements

- PHP 7.4+ (8.x recommended) with extensions: `pdo_mysql`, `json`, `fileinfo`, `curl`
- Composer
- Node.js 18+ (for Tailwind/jQuery assets)
- MySQL 8

## Quick start

### 1. Install dependencies

```bash
composer install
cp .env.example .env
# Edit .env with database credentials, SuperAdmin credentials, and PROPERTY_API_KEY
npm install
npm run build
```

### 2. Database

Create the database, then run migrations and seed:

```bash
mysql -uroot -p -e "CREATE DATABASE slim_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php bin/migrate.php
php bin/seed-superadmin.php
```

Migration `005_properties_api_uuid.sql` adds `api_uuid` for API upserts. Re-running `005` on an already-migrated database will fail; apply it once.

### 3. Run the application

```bash
composer start
```

Open [http://localhost:8080/admin/login](http://localhost:8080/admin/login) and sign in with `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD` from `.env`.

### Docker

```bash
docker compose up -d
docker compose exec slim composer install
docker compose exec slim sh -c "cp .env.example .env && sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env"
docker compose exec slim php bin/migrate.php
docker compose exec slim php bin/seed-superadmin.php
```

## Admin routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin/login` | Login form |
| POST | `/admin/login` | Authenticate |
| POST | `/admin/logout` | Sign out |
| GET | `/admin/properties` | List all properties (manual and API) |
| GET | `/admin/properties/create` | Add form |
| POST | `/admin/properties` | Create property |
| GET | `/admin/properties/{id}/edit` | Edit form |
| POST | `/admin/properties/{id}` | Update property |
| POST | `/admin/properties/{id}/delete` | Soft delete property |

## API property sync

Sync listings from the external API into `properties` with `source = 'api'`. Manual admin rows (`source = 'manual'`) are never modified.

### Environment

| Variable | Description |
|----------|-------------|
| `PROPERTY_API_BASE_URL` | API host (default `https://trial.craig.mtcserver15.com`) |
| `PROPERTY_API_KEY` | Required API key (GET param `api_key`) |
| `PROPERTY_API_PAGE_SIZE` | Results per page (1–100, default 100) |
| `PROPERTY_API_SYNC_MAX_PAGES` | Optional default page cap for CLI |
| `PROPERTY_API_SYNC_LIMIT` | Optional default record cap for CLI |

### CLI

```bash
php bin/sync-api-properties.php --help
```

**Full sync** (production CRON — no caps):

```bash
php bin/sync-api-properties.php
```

**Limited sync** (dev/staging):

```bash
php bin/sync-api-properties.php --max-pages=1
php bin/sync-api-properties.php --max-pages=10
php bin/sync-api-properties.php --limit=100
php bin/sync-api-properties.php --limit=500
php bin/sync-api-properties.php --max-pages=10 --limit=500
```

CLI flags override env defaults. If both `--max-pages` and `--limit` are set, the run stops when either cap is reached first.

Example crontab (full sync every 6 hours):

```cron
0 */6 * * * cd /path/to/slim-app && php bin/sync-api-properties.php >> logs/sync.log 2>&1
```

The trial API has tens of thousands of pages at `page[size]=100`; use limits locally. API rows are upserted by `api_uuid`; listings removed from the API are not deleted from the database (upsert-only).

Images are downloaded over HTTPS into `public/uploads/properties/api/{uuid}/`.

## Security

- Session-based SuperAdmin auth with `AuthMiddleware`
- CSRF tokens on all admin POST forms
- HTTPS redirect in production (`APP_ENV=production`)
- Passwords stored with `password_hash()`
- Output escaped via `ViewHelper::e()`
- Uploaded images validated by MIME type; stored under `public/uploads/properties/`
- API key stored in `.env` only; sync is CLI-only (no public HTTP endpoint)
- Disable PHP execution in upload directories (see `public/uploads/.htaccess`)

## Property data model

- Admin-created listings use `source = 'manual'`
- API sync upserts `source = 'api'` rows keyed by `api_uuid`
- Soft deletes via `deleted_at`
- Future public search should include both sources where `deleted_at IS NULL`

## Production notes

- Set `APP_ENV=production` and terminate TLS at the reverse proxy
- Set strong `SUPERADMIN_PASSWORD` and rotate after first login
- Run `npm run build` as part of deploy
- Ensure `public/uploads/` is writable by the web server and cron user
