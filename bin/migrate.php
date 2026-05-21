<?php

declare(strict_types=1);

/**
 * Run all database/sql/*.sql files in order.
 */
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'] ?? 'slim_app';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Database connection failed: {$e->getMessage()}\n");
    fwrite(STDERR, "Create the database first: CREATE DATABASE {$dbName} CHARACTER SET utf8mb4;\n");
    exit(1);
}

$sqlDir = $root . '/database/sql';
$files = glob($sqlDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    echo "Applying {$name}...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Failed to read {$file}\n");
        exit(1);
    }
    $pdo->exec($sql);
    echo "  OK\n";
}

echo "Migrations complete.\n";
