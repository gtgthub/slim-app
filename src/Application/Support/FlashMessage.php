<?php

declare(strict_types=1);

namespace App\Application\Support;

class FlashMessage
{
    private const SESSION_KEY = 'flash';

    public static function set(string $type, string $message): void
    {
        $_SESSION[self::SESSION_KEY] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /**
     * @return array{type: string, message: string}|null
     */
    public static function pull(): ?array
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        $flash = $_SESSION[self::SESSION_KEY];
        unset($_SESSION[self::SESSION_KEY]);

        if (!is_array($flash) || !isset($flash['type'], $flash['message'])) {
            return null;
        }

        return [
            'type' => (string) $flash['type'],
            'message' => (string) $flash['message'],
        ];
    }
}
