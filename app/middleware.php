<?php

declare(strict_types=1);

use App\Application\Middleware\HttpsRedirectMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(HttpsRedirectMiddleware::class);
    $app->add(SessionMiddleware::class);
};
