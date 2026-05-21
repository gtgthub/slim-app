<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\HtmlAction;
use App\Domain\Auth\AuthenticationService;
use Psr\Http\Message\ResponseInterface as Response;

class LoginAction extends HtmlAction
{
    private AuthenticationService $authenticationService;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        AuthenticationService $authenticationService
    ) {
        parent::__construct($logger, $view);
        $this->authenticationService = $authenticationService;
    }

    protected function action(): Response
    {
        $data = $this->getFormData();
        if (!is_array($data)) {
            $data = [];
        }

        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->render('admin/login.php', [
                'error' => 'Email and password are required.',
                'email' => $email,
            ]);
        }

        $user = $this->authenticationService->authenticate($email, $password);
        if ($user === null) {
            return $this->render('admin/login.php', [
                'error' => 'Invalid credentials or insufficient privileges.',
                'email' => $email,
            ]);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_email'] = $user->getEmail();

        return $this->redirect('/admin/properties');
    }
}
