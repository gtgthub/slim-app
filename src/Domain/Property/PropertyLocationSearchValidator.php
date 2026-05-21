<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertyLocationSearchValidator
{
    private const MAX_LOCATION_LABEL = 255;

    /**
     * @param array<string, mixed> $data
     * @return array<string, string> field => error message
     */
    public function validate(array $data): array
    {
        $errors = [];

        $latRaw = $this->trimString($data['lat'] ?? '');
        $lngRaw = $this->trimString($data['lng'] ?? '');

        if ($latRaw === '') {
            $errors['lat'] = 'Select a location on the map or choose a place from the suggestions.';
        } elseif (!is_numeric($latRaw) || (float) $latRaw < -90 || (float) $latRaw > 90) {
            $errors['lat'] = 'Latitude must be between -90 and 90.';
        }

        if ($lngRaw === '') {
            $errors['lng'] = 'Select a location on the map or choose a place from the suggestions.';
        } elseif (!is_numeric($lngRaw) || (float) $lngRaw < -180 || (float) $lngRaw > 180) {
            $errors['lng'] = 'Longitude must be between -180 and 180.';
        }

        $location = $this->trimString($data['location'] ?? '');
        if ($location !== '' && strlen($location) > self::MAX_LOCATION_LABEL) {
            $errors['location'] = 'Location label must be at most ' . self::MAX_LOCATION_LABEL . ' characters.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed> Normalized values for criteria and form repopulation.
     */
    public function normalize(array $data): array
    {
        $lat = $this->trimString($data['lat'] ?? '');
        $lng = $this->trimString($data['lng'] ?? '');
        $location = $this->trimString($data['location'] ?? '');
        $tab = $this->trimString($data['tab'] ?? '');

        return [
            'lat' => $lat,
            'lng' => $lng,
            'location' => $location,
            'tab' => $tab === 'map' ? 'map' : 'text',
        ];
    }

    /**
     * True when the user submitted the search form (any lat/lng/location param present).
     *
     * @param array<string, mixed> $queryParams
     */
    public function isSearchSubmitted(array $queryParams): bool
    {
        return isset($queryParams['lat'])
            || isset($queryParams['lng'])
            || isset($queryParams['location']);
    }

    private function trimString(string $value): string
    {
        return trim($value);
    }
}
