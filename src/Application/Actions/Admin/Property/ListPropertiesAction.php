<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Support\PaginationQuery;
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
        $pagination = PaginationQuery::fromQueryParams($this->request->getQueryParams());
        $totalCount = $this->propertyRepository->countAll();
        $totalPages = $pagination->totalPages($totalCount);
        $currentPage = $pagination->clampPage($totalCount);

        $properties = $totalCount === 0
            ? []
            : $this->propertyRepository->findPaginated($currentPage, $pagination->getPerPage());

        return $this->render('admin/properties/index.php', [
            'properties' => $properties,
            'pagination' => $pagination,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
        ]);
    }
}
