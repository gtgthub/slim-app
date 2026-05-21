<?php

declare(strict_types=1);

namespace App\Application\Support;

class ViewHelper
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function selected(string $current, string $value): string
    {
        return $current === $value ? ' selected' : '';
    }

    public static function checked(string $current, string $value): string
    {
        return $current === $value ? ' checked' : '';
    }
}
