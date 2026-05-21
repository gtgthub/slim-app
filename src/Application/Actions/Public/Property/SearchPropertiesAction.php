<?php

declare(strict_types=1);

namespace App\Application\Actions\Public\Property;

use App\Application\Actions\HtmlAction;
use App\Application\Support\PaginationQuery;
use App\Domain\Property\PropertyRepository;
use App\Domain\Property\PropertySearchCriteria;
use App\Domain\Property\PropertySearchValidator;
use Psr\Http\Message\ResponseInterface as Response;

class SearchPropertiesAction extends HtmlAction
{
    private PropertyRepository $propertyRepository;

    private PropertySearchValidator $searchValidator;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Slim\Views\PhpRenderer $view,
        PropertyRepository $propertyRepository,
        PropertySearchValidator $searchValidator
    ) {
        parent::__construct($logger, $view);
        $this->propertyRepository = $propertyRepository;
        $this->searchValidator = $searchValidator;
    }

    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $formData = $this->searchValidator->normalize($queryParams);
        $errors = $this->searchValidator->validate($formData);

        $hasValidationErrors = $errors !== [];
        $criteria = $hasValidationErrors
            ? null
            : PropertySearchCriteria::fromValidated($formData);

        $extraQuery = $criteria !== null ? $criteria->toQueryParams() : [];
        $pagination = PaginationQuery::fromQueryParams($queryParams, '/', $extraQuery);

        $totalCount = 0;
        $totalPages = 0;
        $currentPage = 1;
        $properties = [];

        if (!$hasValidationErrors && $criteria !== null) {
            $totalCount = $this->propertyRepository->countBySearch($criteria);
            $totalPages = $pagination->totalPages($totalCount);
            $currentPage = $pagination->clampPage($totalCount);

            if ($totalCount > 0) {
                $properties = $this->propertyRepository->findBySearch(
                    $criteria,
                    $currentPage,
                    $pagination->getPerPage()
                );
            }
        }

        return $this->render('public/properties/search.php', [
            'formData' => $formData,
            'errors' => $errors,
            'properties' => $properties,
            'pagination' => $pagination,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'hasValidationErrors' => $hasValidationErrors,
        ]);
    }
}
