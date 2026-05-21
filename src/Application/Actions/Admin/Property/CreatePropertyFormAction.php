<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Property;

use App\Application\Actions\HtmlAction;
use App\Domain\Property\Property;
use Psr\Http\Message\ResponseInterface as Response;

class CreatePropertyFormAction extends HtmlAction
{
    protected function action(): Response
    {
        return $this->render('admin/properties/form.php', [
            'property' => null,
            'errors' => [],
            'formData' => $this->defaultFormData(),
            'isEdit' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormData(): array
    {
        return [
            'county' => '',
            'country' => '',
            'town' => '',
            'description' => '',
            'displayable_address' => '',
            'latitude' => '',
            'longitude' => '',
            'bedrooms' => '',
            'bathrooms' => '',
            'price' => '',
            'property_type' => 'house',
            'listing_type' => Property::LISTING_SALE,
        ];
    }
}
