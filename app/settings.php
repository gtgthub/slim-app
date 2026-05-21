<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $env = static function (string $key, ?string $default = null): ?string {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        $value = getenv($key);

        if ($value !== false && $value !== '') {
            return (string) $value;
        }

        return $default;
    };

    $isProduction = ($env('APP_ENV', 'development') ?? 'development') === 'production';

    $optionalPositiveInt = static function (?string $value): ?int {
        if ($value === null || $value === '') {
            return null;
        }
        $n = (int) $value;

        return $n > 0 ? $n : null;
    };

    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () use ($isProduction, $optionalPositiveInt, $env) {
            return new Settings([
                'displayErrorDetails' => !$isProduction,
                'logError' => $isProduction,
                'logErrorDetails' => $isProduction,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => $env('docker') !== null ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'app' => [
                    'env' => $env('APP_ENV', 'development') ?? 'development',
                    'url' => $env('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080',
                ],
                'session' => [
                    'name' => $env('SESSION_NAME', 'slim_app_session') ?? 'slim_app_session',
                ],
                'db' => [
                    'host' => $env('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
                    'port' => $env('DB_PORT', '3306') ?? '3306',
                    'name' => $env('DB_NAME', 'slim_app') ?? 'slim_app',
                    'user' => $env('DB_USER', 'root') ?? 'root',
                    'pass' => $env('DB_PASS', '') ?? '',
                ],
                'template' => [
                    'path' => __DIR__ . '/../templates',
                ],
                'upload' => [
                    'root' => __DIR__ . '/..',
                ],
                'property_api' => [
                    'base_url' => rtrim($env('PROPERTY_API_BASE_URL', 'https://trial.craig.mtcserver15.com') ?? 'https://trial.craig.mtcserver15.com', '/'),
                    'api_key' => $env('PROPERTY_API_KEY', '') ?? '',
                    'page_size' => min(100, max(1, (int) ($env('PROPERTY_API_PAGE_SIZE', '100') ?? '100'))),
                    'sync_max_pages' => $optionalPositiveInt($env('PROPERTY_API_SYNC_MAX_PAGES')),
                    'sync_limit' => $optionalPositiveInt($env('PROPERTY_API_SYNC_LIMIT')),
                ],
                'google_maps' => [
                    'api_key' => $env('GOOGLE_MAPS_API_KEY', '') ?? '',
                    'default_lat' => (float) ($env('GOOGLE_MAPS_DEFAULT_LAT', '53.3498') ?? '53.3498'),
                    'default_lng' => (float) ($env('GOOGLE_MAPS_DEFAULT_LNG', '-6.2603') ?? '-6.2603'),
                ],
            ]);
        },
    ]);
};
