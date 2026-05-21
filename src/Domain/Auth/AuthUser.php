<?php

declare(strict_types=1);

namespace App\Domain\Auth;

class AuthUser
{
    private int $id;

    private string $email;

    private string $passwordHash;

    private string $name;

    private bool $isActive;

    private Role $role;

    public function __construct(
        int $id,
        string $email,
        string $passwordHash,
        string $name,
        bool $isActive,
        Role $role
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role->isSuperAdmin();
    }
}
