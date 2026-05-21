<?php

declare(strict_types=1);

namespace App\Domain\Auth;

class AuthenticationService
{
    private AuthUserRepository $authUserRepository;

    public function __construct(AuthUserRepository $authUserRepository)
    {
        $this->authUserRepository = $authUserRepository;
    }

    public function authenticate(string $email, string $password): ?AuthUser
    {
        $user = $this->authUserRepository->findByEmail($email);

        if ($user === null || !$user->isActive() || !$user->isSuperAdmin()) {
            return null;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return null;
        }

        $this->authUserRepository->updateLastLogin($user->getId());

        return $user;
    }
}
