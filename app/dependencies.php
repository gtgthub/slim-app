<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Support\PropertyUploadFailureHandler;
use App\Domain\Auth\AuthenticationService;
use App\Domain\Property\PropertyValidator;
use App\Infrastructure\Upload\PropertyFileUploader;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\PhpRenderer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        \PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $db = $settings->get('db');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            return new \PDO($dsn, $db['user'], $db['pass'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        },
        PhpRenderer::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $template = $settings->get('template');

            return new PhpRenderer($template['path']);
        },
        AuthenticationService::class => \DI\autowire(),
        PropertyValidator::class => \DI\autowire(),
        PropertyUploadFailureHandler::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            return new PropertyUploadFailureHandler(
                $c->get(LoggerInterface::class),
                $settings->get('displayErrorDetails')
            );
        },
        PropertyFileUploader::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $upload = $settings->get('upload');

            return new PropertyFileUploader($upload['root']);
        },
    ]);
};
