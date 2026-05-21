<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class ViewPropertyDetailAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
    }

    protected function action(): Response
    {
        $id = (int) $this->resolveArg('id');
        $property = $this->propertyRepository->findManualById($id);

        $html = $this->view->fetch('admin/properties/_detail_content.php', [
            'property' => $property,
        ]);

        $this->response->getBody()->write($html);

        return $this->response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
