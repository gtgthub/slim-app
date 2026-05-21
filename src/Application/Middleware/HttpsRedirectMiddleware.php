<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class HttpsRedirectMiddleware implements MiddlewareInterface
{
    private SettingsInterface $settings;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = $this->settings->get('app');
        if (($app['env'] ?? 'development') !== 'production') {
            return $handler->handle($request);
        }

        if ($this->isHttps($request)) {
            return $handler->handle($request);
        }

        $uri = $request->getUri()->withScheme('https');
        $response = new Response();

        return $response
            ->withHeader('Location', (string) $uri)
            ->withStatus(301);
    }

    private function isHttps(ServerRequestInterface $request): bool
    {
        $uri = $request->getUri();
        if ($uri->getScheme() === 'https') {
            return true;
        }

        return strtolower($request->getHeaderLine('X-Forwarded-Proto')) === 'https';
    }
}
