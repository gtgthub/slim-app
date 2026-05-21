<?php

declare(strict_types=1);

namespace App\Application\Support;

class CsrfToken
{
    private const SESSION_KEY = 'csrf_token';

    public static function getOrCreate(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::SESSION_KEY];
    }

    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY] ?? '';

        return is_string($stored) && hash_equals($stored, $token);
    }
}
