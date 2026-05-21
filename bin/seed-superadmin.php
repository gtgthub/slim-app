<?php

declare(strict_types=1);

/**
 * Create or update the SuperAdmin user from .env credentials.
 */
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$email = $_ENV['SUPERADMIN_EMAIL'] ?? '';
$password = $_ENV['SUPERADMIN_PASSWORD'] ?? '';
$name = $_ENV['SUPERADMIN_NAME'] ?? 'Super Admin';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD in .env\n");
    exit(1);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'] ?? 'slim_app';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$roleStmt = $pdo->query("SELECT id FROM roles WHERE name = 'superadmin' LIMIT 1");
$roleId = $roleStmt->fetchColumn();

if ($roleId === false) {
    fwrite(STDERR, "superadmin role not found. Run bin/migrate.php first.\n");
    exit(1);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$check->execute(['email' => $email]);
$existingId = $check->fetchColumn();

if ($existingId !== false) {
    $update = $pdo->prepare(
        'UPDATE users SET role_id = :role_id, password_hash = :password_hash, name = :name, is_active = 1 WHERE id = :id'
    );
    $update->execute([
        'role_id' => $roleId,
        'password_hash' => $passwordHash,
        'name' => $name,
        'id' => $existingId,
    ]);
    echo "SuperAdmin user updated (id={$existingId}).\n";
} else {
    $insert = $pdo->prepare(
        'INSERT INTO users (role_id, email, password_hash, name, is_active) VALUES (:role_id, :email, :password_hash, :name, 1)'
    );
    $insert->execute([
        'role_id' => $roleId,
        'email' => $email,
        'password_hash' => $passwordHash,
        'name' => $name,
    ]);
    echo 'SuperAdmin user created (id=' . $pdo->lastInsertId() . ").\n";
}
