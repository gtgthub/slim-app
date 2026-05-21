<?php

declare(strict_types=1);

use App\Application\Actions\Admin\LoginAction;
use App\Application\Actions\Admin\LoginFormAction;
use App\Application\Actions\Admin\LogoutAction;
use App\Application\Actions\Admin\Property\CreatePropertyAction;
use App\Application\Actions\Admin\Property\CreatePropertyFormAction;
use App\Application\Actions\Admin\Property\DeletePropertyAction;
use App\Application\Actions\Admin\Property\EditPropertyFormAction;
use App\Application\Actions\Admin\Property\ListPropertiesAction;
use App\Application\Actions\Admin\Property\UpdatePropertyAction;
use App\Application\Actions\Admin\Property\ViewPropertyDetailAction;
use App\Application\Actions\Public\Property\AddressAutocompleteAction;
use App\Application\Actions\Public\Property\SearchPropertiesAction;
use App\Application\Actions\Public\Property\ViewPublicPropertyAction;
use App\Application\Middleware\AuthMiddleware;
use App\Application\Middleware\CsrfMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->get('/', SearchPropertiesAction::class);
    $app->get('/properties/addresses/autocomplete', AddressAutocompleteAction::class);
    $app->get('/properties/{id}', ViewPublicPropertyAction::class);

    $app->group('/admin', function (Group $group) {
        $group->get('', function (Request $request, Response $response) {
            return $response->withHeader('Location', '/admin/properties')->withStatus(302);
        });

        $group->get('/login', LoginFormAction::class);
        $group->post('/login', LoginAction::class);
        $group->post('/logout', LogoutAction::class);

        $group->get('/properties', ListPropertiesAction::class);
        $group->get('/properties/create', CreatePropertyFormAction::class);
        $group->post('/properties', CreatePropertyAction::class);
        $group->get('/properties/{id}', ViewPropertyDetailAction::class);
        $group->get('/properties/{id}/edit', EditPropertyFormAction::class);
        $group->post('/properties/{id}', UpdatePropertyAction::class);
        $group->post('/properties/{id}/delete', DeletePropertyAction::class);
    })->add(CsrfMiddleware::class)->add(AuthMiddleware::class);
};
