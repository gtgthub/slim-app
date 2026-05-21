<?php

declare(strict_types=1);

namespace App\Domain\Property;

use Psr\Log\LoggerInterface;

class PropertyApiMapper
{
    private LoggerInterface $logger;

    /** @var array<string, string> */
    private const TYPE_MAP = [
        'flat' => 'flat',
        'bungalow' => 'bungalow',
        'terraced' => 'house',
        'semi-detached' => 'house',
        'semi detached' => 'house',
        'detached' => 'house',
        'detatched' => 'house',
        'cottage' => 'house',
        'end of terrace' => 'house',
        'apartment' => 'apartment',
        'land' => 'land',
        'commercial' => 'commercial',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array<string, mixed> $item
     */
    public function map(array $item): Property
    {
        $uuid = trim((string) ($item['uuid'] ?? ''));
        $propertyTypeTitle = '';
        if (isset($item['property_type']) && is_array($item['property_type'])) {
            $propertyTypeTitle = trim((string) ($item['property_type']['title'] ?? ''));
        }

        $lat = $this->optionalFloat($item['latitude'] ?? null);
        $lng = $this->optionalFloat($item['longitude'] ?? null);

        return new Property(
            null,
            Property::SOURCE_API,
            $uuid,
            trim((string) ($item['county'] ?? '')),
            trim((string) ($item['country'] ?? '')),
            trim((string) ($item['town'] ?? '')),
            trim((string) ($item['description'] ?? '')),
            trim((string) ($item['address'] ?? '')),
            null,
            null,
            $lat,
            $lng,
            (int) ($item['num_bedrooms'] ?? 0),
            (int) ($item['num_bathrooms'] ?? 0),
            (float) ($item['price'] ?? 0),
            $this->mapPropertyType($propertyTypeTitle),
            $this->mapListingType((string) ($item['type'] ?? '')),
            null
        );
    }

    /**
     * @return array{image_full: string|null, image_thumbnail: string|null}
     */
    public function extractImageUrls(array $item): array
    {
        $full = trim((string) ($item['image_full'] ?? ''));
        $thumb = trim((string) ($item['image_thumbnail'] ?? ''));

        return [
            'image_full' => $full !== '' ? $full : null,
            'image_thumbnail' => $thumb !== '' ? $thumb : null,
        ];
    }

    private function mapPropertyType(string $title): string
    {
        if ($title === '') {
            return 'house';
        }

        $key = strtolower($title);
        if (isset(self::TYPE_MAP[$key])) {
            return self::TYPE_MAP[$key];
        }

        $this->logger->warning('Unknown API property type; defaulting to house.', [
            'title' => $title,
        ]);

        return 'house';
    }

    private function mapListingType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return $normalized === Property::LISTING_RENT ? Property::LISTING_RENT : Property::LISTING_SALE;
    }

    /**
     * @param mixed $value
     */
    private function optionalFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
