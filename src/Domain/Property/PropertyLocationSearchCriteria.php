<?php

declare(strict_types=1);

namespace App\Domain\Property;

final class PropertyLocationSearchCriteria
{
    public const RADIUS_KM = 10.0;

    public const MAX_RESULTS = 250;

    private float $latitude;

    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param array<string, mixed> $validated Normalized form/query values after validation.
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            (float) $validated['lat'],
            (float) $validated['lng']
        );
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return array<string, string> Query string params for form repopulation.
     */
    public function toQueryParams(?string $locationLabel = null): array
    {
        $params = [
            'lat' => self::formatCoordinate($this->latitude),
            'lng' => self::formatCoordinate($this->longitude),
        ];

        if ($locationLabel !== null && $locationLabel !== '') {
            $params['location'] = $locationLabel;
        }

        return $params;
    }

    private static function formatCoordinate(float $value): string
    {
        return rtrim(rtrim(sprintf('%.7f', $value), '0'), '.');
    }
}
