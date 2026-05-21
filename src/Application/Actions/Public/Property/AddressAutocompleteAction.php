<?php

declare(strict_types=1);

namespace App\Application\Actions\Public\Property;

use App\Application\Actions\Action;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class AddressAutocompleteAction extends Action
{
    private const MIN_TERM_LENGTH = 2;

    private const MAX_TERM_LENGTH = 100;

    private const SUGGESTION_LIMIT = 15;

    private PropertyRepository $propertyRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        PropertyRepository $propertyRepository
    ) {
        parent::__construct($logger);
        $this->propertyRepository = $propertyRepository;
    }

    protected function action(): Response
    {
        $params = $this->request->getQueryParams();
        $term = trim((string) ($params['q'] ?? ''));

        if (strlen($term) < self::MIN_TERM_LENGTH || strlen($term) > self::MAX_TERM_LENGTH) {
            return $this->respondJsonArray([]);
        }

        $addresses = $this->propertyRepository->findDisplayableAddressesLike(
            $term,
            self::SUGGESTION_LIMIT
        );

        return $this->respondJsonArray($addresses);
    }

    /**
     * @param string[] $data
     */
    private function respondJsonArray(array $data): Response
    {
        $this->response->getBody()->write((string) json_encode($data));

        return $this->response->withHeader('Content-Type', 'application/json');
    }
}
