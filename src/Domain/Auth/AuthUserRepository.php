<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface AuthUserRepository
{
    public function findByEmail(string $email): ?AuthUser;

    public function findById(int $id): ?AuthUser;

    public function updateLastLogin(int $userId): void;
}
