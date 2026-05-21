<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () use ($isProduction) {
            return new Settings([
                'displayErrorDetails' => !$isProduction,
                'logError' => $isProduction,
                'logErrorDetails' => $isProduction,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'app' => [
                    'env' => $_ENV['APP_ENV'] ?? 'development',
                    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
                ],
                'session' => [
                    'name' => $_ENV['SESSION_NAME'] ?? 'slim_app_session',
                ],
                'db' => [
                    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['DB_PORT'] ?? '3306',
                    'name' => $_ENV['DB_NAME'] ?? 'slim_app',
                    'user' => $_ENV['DB_USER'] ?? 'root',
                    'pass' => $_ENV['DB_PASS'] ?? '',
                ],
                'template' => [
                    'path' => __DIR__ . '/../templates',
                ],
                'upload' => [
                    'root' => __DIR__ . '/..',
                ],
            ]);
        },
    ]);
};
