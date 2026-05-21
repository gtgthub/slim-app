-- Seed SuperAdmin user. Password hash is set by bin/seed-superadmin.php from .env
-- This file only ensures the role exists; user row is created by the seed script.

INSERT INTO roles (name) VALUES ('superadmin')
ON DUPLICATE KEY UPDATE name = name;
