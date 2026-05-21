<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Support\CsrfToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        if (strpos($path, '/admin') !== 0) {
            return $handler->handle($request);
        }

        $body = $request->getParsedBody();
        $token = null;
        if (is_array($body) && isset($body['_csrf'])) {
            $token = (string) $body['_csrf'];
        }

        if (!CsrfToken::validate($token)) {
            $response = new Response();
            $response->getBody()->write('Invalid CSRF token.');

            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
