# Database migrations
SQL files are applied in numeric order. All scripts use `CREATE TABLE IF NOT EXISTS` and are safe to re-run for DDL.

## Apply order
1. `001_roles.sql` — roles table + superadmin role
2. `002_users.sql` — users table
3. `003_properties.sql` — properties table
4. `004_seed_superadmin.sql` — ensures superadmin role exists
5. `005_properties_api_uuid.sql` — `api_uuid` column for API sync upsert

After migrations, run the SuperAdmin seed script:
php bin/migrate.php
php bin/seed-superadmin.php


## Manual apply (MySQL CLI)
mysql -h127.0.0.1 -uroot -p slim_app < database/sql/001_roles.sql
mysql -h127.0.0.1 -uroot -p slim_app < database/sql/002_users.sql
mysql -h127.0.0.1 -uroot -p slim_app < database/sql/003_properties.sql
mysql -h127.0.0.1 -uroot -p slim_app < database/sql/004_seed_superadmin.sql
php bin/seed-superadmin.php

## Rollback (development only)
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

## Notes
- Admin-created properties use `source = 'manual'`.
- API sync upserts rows with `source = 'api'` keyed by `api_uuid`; manual rows are never modified.
- Soft deletes set `deleted_at`; list queries exclude deleted rows.