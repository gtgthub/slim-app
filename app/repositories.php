<?php

declare(strict_types=1);

use App\Domain\Auth\AuthUserRepository;
use App\Domain\Property\PropertyRepository;
use App\Infrastructure\Persistence\Auth\PdoAuthUserRepository;
use App\Infrastructure\Persistence\Property\PdoPropertyRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        AuthUserRepository::class => \DI\autowire(PdoAuthUserRepository::class),
        PropertyRepository::class => \DI\autowire(PdoPropertyRepository::class),
    ]);
};
