<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    private SettingsInterface $settings;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (strpos($path, '/admin') !== 0) {
            return $handler->handle($request);
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $session = $this->settings->get('session');
            session_name((string) $session['name']);

            $isSecure = $this->isHttps($request);
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }

        return $handler->handle($request);
    }

    private function isHttps(ServerRequestInterface $request): bool
    {
        $uri = $request->getUri();

        if ($uri->getScheme() === 'https') {
            return true;
        }

        $forwarded = $request->getHeaderLine('X-Forwarded-Proto');

        return strtolower($forwarded) === 'https';
    }
}
