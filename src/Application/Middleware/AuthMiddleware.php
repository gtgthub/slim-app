<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\Auth\AuthUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthUserRepository $authUserRepository;

    public function __construct(AuthUserRepository $authUserRepository)
    {
        $this->authUserRepository = $authUserRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if ($this->isPublicAdminPath($path)) {
            return $handler->handle($request);
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return $this->redirect('/admin/login');
        }

        $user = $this->authUserRepository->findById((int) $userId);
        if ($user === null || !$user->isActive() || !$user->isSuperAdmin()) {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

            return $this->redirect('/admin/login');
        }

        return $handler->handle($request->withAttribute('auth_user', $user));
    }

    private function isPublicAdminPath(string $path): bool
    {
        return $path === '/admin/login';
    }

    private function redirect(string $location): ResponseInterface
    {
        $response = new Response();

        return $response
            ->withHeader('Location', $location)
            ->withStatus(302);
    }
}
