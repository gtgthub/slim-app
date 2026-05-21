<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

function buildAppContainer(): ContainerInterface
{
    $containerBuilder = new ContainerBuilder();

    if (false) { // Should be set to true in production
        $containerBuilder->enableCompilation(dirname(__DIR__) . '/var/cache');
    }

    $settings = require __DIR__ . '/settings.php';
    $settings($containerBuilder);

    $dependencies = require __DIR__ . '/dependencies.php';
    $dependencies($containerBuilder);

    $repositories = require __DIR__ . '/repositories.php';
    $repositories($containerBuilder);

    return $containerBuilder->build();
}

return buildAppContainer();
