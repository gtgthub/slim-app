<?php

declare(strict_types=1);

namespace App\Domain\Property;

class Property
{
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_API = 'api';

    public const PROPERTY_TYPES = [
        'house' => 'House',
        'flat' => 'Flat',
        'apartment' => 'Apartment',
        'bungalow' => 'Bungalow',
        'land' => 'Land',
        'commercial' => 'Commercial',
    ];

    public const LISTING_SALE = 'sale';

    public const LISTING_RENT = 'rent';

    private ?int $id;

    private string $source;

    private ?string $apiUuid;

    private string $county;

    private string $country;

    private string $town;

    private string $description;

    private string $displayableAddress;

    private ?string $imagePath;

    private ?string $thumbnailPath;

    private ?float $latitude;

    private ?float $longitude;

    private int $bedrooms;

    private int $bathrooms;

    private float $price;

    private string $propertyType;

    private string $listingType;

    private ?int $createdBy;

    public function __construct(
        ?int $id,
        string $source,
        ?string $apiUuid,
        string $county,
        string $country,
        string $town,
        string $description,
        string $displayableAddress,
        ?string $imagePath,
        ?string $thumbnailPath,
        ?float $latitude,
        ?float $longitude,
        int $bedrooms,
        int $bathrooms,
        float $price,
        string $propertyType,
        string $listingType,
        ?int $createdBy
    ) {
        $this->id = $id;
        $this->source = $source;
        $this->apiUuid = $apiUuid;
        $this->county = $county;
        $this->country = $country;
        $this->town = $town;
        $this->description = $description;
        $this->displayableAddress = $displayableAddress;
        $this->imagePath = $imagePath;
        $this->thumbnailPath = $thumbnailPath;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->bedrooms = $bedrooms;
        $this->bathrooms = $bathrooms;
        $this->price = $price;
        $this->propertyType = $propertyType;
        $this->listingType = $listingType;
        $this->createdBy = $createdBy;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getApiUuid(): ?string
    {
        return $this->apiUuid;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getTown(): string
    {
        return $this->town;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDisplayableAddress(): string
    {
        return $this->displayableAddress;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getBedrooms(): int
    {
        return $this->bedrooms;
    }

    public function getBathrooms(): int
    {
        return $this->bathrooms;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    public function getListingType(): string
    {
        return $this->listingType;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getPropertyTypeLabel(): string
    {
        return self::PROPERTY_TYPES[$this->propertyType] ?? $this->propertyType;
    }

    public function getListingTypeLabel(): string
    {
        return $this->listingType === self::LISTING_SALE ? 'For Sale' : 'For Rent';
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withImagePaths(?string $imagePath, ?string $thumbnailPath): self
    {
        $clone = clone $this;
        $clone->imagePath = $imagePath;
        $clone->thumbnailPath = $thumbnailPath;

        return $clone;
    }
}
