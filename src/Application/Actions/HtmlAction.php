<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Support\CsrfToken;
use App\Application\Support\FlashMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

abstract class HtmlAction extends Action
{
    protected PhpRenderer $view;

    public function __construct(\Psr\Log\LoggerInterface $logger, PhpRenderer $view)
    {
        parent::__construct($logger);
        $this->view = $view;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = []): Response
    {
        $data['csrf'] = CsrfToken::getOrCreate();
        $data['flash'] = FlashMessage::pull();
        $data['authUserName'] = $_SESSION['user_name'] ?? null;

        return $this->view->render($this->response, $template, $data);
    }

    protected function redirect(string $url): Response
    {
        return $this->response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    protected function getAuthUserId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }
}
