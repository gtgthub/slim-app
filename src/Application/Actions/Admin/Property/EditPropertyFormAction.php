<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Domain\Property\PropertyRepository;
use Psr\Http\Message\ResponseInterface as Response;

class EditPropertyFormAction extends HtmlAction
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
        $property = $this->propertyRepository->findById($id);

        return $this->render('admin/properties/form.php', [
            'property' => $property,
            'errors' => [],
            'formData' => $this->propertyToFormData($property),
            'isEdit' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyToFormData(\App\Domain\Property\Property $property): array
    {
        return [
            'county' => $property->getCounty(),
            'country' => $property->getCountry(),
            'town' => $property->getTown(),
            'description' => $property->getDescription(),
            'displayable_address' => $property->getDisplayableAddress(),
            'latitude' => $property->getLatitude() !== null ? (string) $property->getLatitude() : '',
            'longitude' => $property->getLongitude() !== null ? (string) $property->getLongitude() : '',
            'bedrooms' => (string) $property->getBedrooms(),
            'bathrooms' => (string) $property->getBathrooms(),
            'price' => (string) $property->getPrice(),
            'property_type' => $property->getPropertyType(),
            'listing_type' => $property->getListingType(),
        ];
    }
}
