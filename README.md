# Real Estate Admin (Slim 4)

Phase 1: SuperAdmin authentication and manual property CRUD with MySQL, Tailwind CSS, and jQuery.

## Requirements

- PHP 7.4+ (8.x recommended) with extensions: `pdo_mysql`, `json`, `fileinfo`
- Composer
- Node.js 18+ (for Tailwind/jQuery assets)
- MySQL 8

## Quick start

### 1. Install dependencies

```bash
composer install
cp .env.example .env
# Edit .env with database credentials and SuperAdmin credentials
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
| GET | `/admin/properties` | List properties |
| GET | `/admin/properties/create` | Add form |
| POST | `/admin/properties` | Create property |
| GET | `/admin/properties/{id}/edit` | Edit form |
| POST | `/admin/properties/{id}` | Update property |
| POST | `/admin/properties/{id}/delete` | Soft delete property |

## Security

- Session-based SuperAdmin auth with `AuthMiddleware`
- CSRF tokens on all admin POST forms
- HTTPS redirect in production (`APP_ENV=production`)
- Passwords stored with `password_hash()`
- Output escaped via `ViewHelper::e()`
- Uploaded images validated by MIME type; stored under `public/uploads/properties/`
- Disable PHP execution in upload directories (see `public/uploads/.htaccess`)

## Property data model

- Admin-created listings use `source = 'manual'`
- Future API sync must only modify `source = 'api'` rows
- Soft deletes via `deleted_at`

## Production notes

- Set `APP_ENV=production` and terminate TLS at the reverse proxy
- Set strong `SUPERADMIN_PASSWORD` and rotate after first login
- Run `npm run build` as part of deploy
- Ensure `public/uploads/` is writable by the web server
