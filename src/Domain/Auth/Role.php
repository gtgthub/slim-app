<?php

declare(strict_types=1);

namespace App\Domain\Auth;

class Role
{
    public const SUPERADMIN = 'superadmin';

    private int $id;

    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === self::SUPERADMIN;
    }
}
