<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\HtmlAction;
use Psr\Http\Message\ResponseInterface as Response;

class LoginFormAction extends HtmlAction
{
    protected function action(): Response
    {
        if (!empty($_SESSION['user_id'])) {
            return $this->redirect('/admin/properties');
        }

        return $this->render('admin/login.php', [
            'error' => null,
            'email' => '',
        ]);
    }
}
