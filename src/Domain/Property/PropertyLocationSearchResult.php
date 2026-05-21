<?php

declare(strict_types=1);

namespace App\Domain\Property;

final class PropertyLocationSearchResult
{
    private Property $property;

    private float $distanceKm;

    public function __construct(Property $property, float $distanceKm)
    {
        $this->property = $property;
        $this->distanceKm = $distanceKm;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function getDistanceKm(): float
    {
        return $this->distanceKm;
    }
}
