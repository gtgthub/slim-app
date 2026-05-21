<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertyApiRecordValidator
{
    private const MAX_TEXT = 100;

    private const MAX_ADDRESS = 255;

    private const MAX_DESCRIPTION = 5000;

    private const MAX_BED_BATH = 20;

    /**
     * @return array<string, string> field => error message
     */
    public function validate(Property $property): array
    {
        $errors = [];

        $uuid = $property->getApiUuid() ?? '';
        if ($uuid === '' || strlen($uuid) > 36) {
            $errors['uuid'] = 'Valid API uuid is required.';
        }

        if ($property->getCounty() === '' || strlen($property->getCounty()) > self::MAX_TEXT) {
            $errors['county'] = 'County is required (max ' . self::MAX_TEXT . ' characters).';
        }

        if ($property->getCountry() === '' || strlen($property->getCountry()) > self::MAX_TEXT) {
            $errors['country'] = 'Country is required (max ' . self::MAX_TEXT . ' characters).';
        }

        if ($property->getTown() === '' || strlen($property->getTown()) > self::MAX_TEXT) {
            $errors['town'] = 'Town is required (max ' . self::MAX_TEXT . ' characters).';
        }

        if ($property->getDescription() === '' || strlen($property->getDescription()) > self::MAX_DESCRIPTION) {
            $errors['description'] = 'Description is required (max ' . self::MAX_DESCRIPTION . ' characters).';
        }

        if ($property->getDisplayableAddress() === '' || strlen($property->getDisplayableAddress()) > self::MAX_ADDRESS) {
            $errors['displayable_address'] = 'Address is required (max ' . self::MAX_ADDRESS . ' characters).';
        }

        $lat = $property->getLatitude();
        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            $errors['latitude'] = 'Latitude must be between -90 and 90.';
        }

        $lng = $property->getLongitude();
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            $errors['longitude'] = 'Longitude must be between -180 and 180.';
        }

        if ($property->getBedrooms() < 0 || $property->getBedrooms() > self::MAX_BED_BATH) {
            $errors['bedrooms'] = 'Invalid number of bedrooms.';
        }

        if ($property->getBathrooms() < 0 || $property->getBathrooms() > self::MAX_BED_BATH) {
            $errors['bathrooms'] = 'Invalid number of bathrooms.';
        }

        if ($property->getPrice() < 0) {
            $errors['price'] = 'Price must be non-negative.';
        }

        if (!array_key_exists($property->getPropertyType(), Property::PROPERTY_TYPES)) {
            $errors['property_type'] = 'Invalid property type.';
        }

        if (!in_array($property->getListingType(), [Property::LISTING_SALE, Property::LISTING_RENT], true)) {
            $errors['listing_type'] = 'Invalid listing type.';
        }

        return $errors;
    }
}
