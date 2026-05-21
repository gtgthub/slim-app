<?php

declare(strict_types=1);

namespace App\Domain\Property;

final class PropertySearchCriteria
{
    private ?string $displayableAddress;

    private ?int $minBedrooms;

    private ?int $minBathrooms;

    private ?float $minPrice;

    public function __construct(
        ?string $displayableAddress = null,
        ?int $minBedrooms = null,
        ?int $minBathrooms = null,
        ?float $minPrice = null
    ) {
        $this->displayableAddress = $displayableAddress;
        $this->minBedrooms = $minBedrooms;
        $this->minBathrooms = $minBathrooms;
        $this->minPrice = $minPrice;
    }

    /**
     * @param array<string, mixed> $validated Normalized form/query values after validation.
     */
    public static function fromValidated(array $validated): self
    {
        $address = self::optionalTrimmedString($validated['displayable_address'] ?? null);
        $bedrooms = self::optionalInt($validated['bedrooms'] ?? null);
        $bathrooms = self::optionalInt($validated['bathrooms'] ?? null);
        $price = self::optionalFloat($validated['price'] ?? null);

        return new self($address, $bedrooms, $bathrooms, $price);
    }

    public function getDisplayableAddress(): ?string
    {
        return $this->displayableAddress;
    }

    public function getMinBedrooms(): ?int
    {
        return $this->minBedrooms;
    }

    public function getMinBathrooms(): ?int
    {
        return $this->minBathrooms;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    /**
     * @return array<string, string> Query string params for pagination links.
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->displayableAddress !== null) {
            $params['displayable_address'] = $this->displayableAddress;
        }
        if ($this->minBedrooms !== null) {
            $params['bedrooms'] = (string) $this->minBedrooms;
        }
        if ($this->minBathrooms !== null) {
            $params['bathrooms'] = (string) $this->minBathrooms;
        }
        if ($this->minPrice !== null) {
            $params['price'] = self::formatPriceForQuery($this->minPrice);
        }

        return $params;
    }

    private static function formatPriceForQuery(float $price): string
    {
        if (floor($price) === $price) {
            return (string) (int) $price;
        }

        return rtrim(rtrim(sprintf('%.2f', $price), '0'), '.');
    }

    /**
     * @param mixed $value
     */
    private static function optionalTrimmedString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param mixed $value
     */
    private static function optionalInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param mixed $value
     */
    private static function optionalFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
