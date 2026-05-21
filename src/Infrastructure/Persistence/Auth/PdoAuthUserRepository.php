<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\AuthUser;
use App\Domain\Auth\AuthUserRepository;
use App\Domain\Auth\Role;
use PDO;

class PdoAuthUserRepository implements AuthUserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?AuthUser
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.email, u.password_hash, u.name, u.is_active, r.id AS role_id, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findById(int $id): ?AuthUser
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.email, u.password_hash, u.name, u.is_active, r.id AS role_id, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): AuthUser
    {
        $role = new Role((int) $row['role_id'], (string) $row['role_name']);

        return new AuthUser(
            (int) $row['id'],
            (string) $row['email'],
            (string) $row['password_hash'],
            (string) $row['name'],
            (bool) $row['is_active'],
            $role
        );
    }
}
