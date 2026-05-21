<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class ListPropertiesAction extends HtmlAction
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
        $properties = $this->propertyRepository->findAllManual();

        return $this->render('admin/properties/index.php', [
            'properties' => $properties,
        ]);
    }
}
