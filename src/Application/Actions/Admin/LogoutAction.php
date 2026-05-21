<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\HtmlAction;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutAction extends HtmlAction
{
    protected function action(): Response
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        return $this->redirect('/admin/login');
    }
}
