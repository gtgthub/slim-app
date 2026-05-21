<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Property;

use App\Domain\Property\Property;
use App\Domain\Property\PropertyNotFoundException;
use App\Domain\Property\PropertyRepository;
use PDO;

class PdoPropertyRepository implements PropertyRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllManual(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM properties
             WHERE source = 'manual' AND deleted_at IS NULL
             ORDER BY created_at DESC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hydrate'], $rows);
    }

    public function findManualById(int $id): Property
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM properties
             WHERE id = :id AND source = 'manual' AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new PropertyNotFoundException();
        }

        return $this->hydrate($row);
    }

    public function create(Property $property): Property
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO properties (
                source, county, country, town, description, displayable_address,
                image_path, thumbnail_path, latitude, longitude,
                bedrooms, bathrooms, price, property_type, listing_type, created_by
            ) VALUES (
                :source, :county, :country, :town, :description, :displayable_address,
                :image_path, :thumbnail_path, :latitude, :longitude,
                :bedrooms, :bathrooms, :price, :property_type, :listing_type, :created_by
            )'
        );
        $stmt->execute($this->toParams($property));
        $id = (int) $this->pdo->lastInsertId();

        return $property->withId($id);
    }

    public function update(Property $property): Property
    {
        if ($property->getId() === null) {
            throw new PropertyNotFoundException();
        }

        $stmt = $this->pdo->prepare(
            "UPDATE properties SET
                county = :county, country = :country, town = :town,
                description = :description, displayable_address = :displayable_address,
                image_path = :image_path, thumbnail_path = :thumbnail_path,
                latitude = :latitude, longitude = :longitude,
                bedrooms = :bedrooms, bathrooms = :bathrooms, price = :price,
                property_type = :property_type, listing_type = :listing_type
             WHERE id = :id AND source = 'manual' AND deleted_at IS NULL"
        );
        $params = $this->toUpdateParams($property);
        $params['id'] = $property->getId();
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new PropertyNotFoundException();
        }

        return $property;
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE properties SET deleted_at = NOW()
             WHERE id = :id AND source = 'manual' AND deleted_at IS NULL"
        );
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() === 0) {
            throw new PropertyNotFoundException();
        }
    }

    public function forceDelete(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM properties WHERE id = :id AND source = 'manual'"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Property
    {
        return new Property(
            (int) $row['id'],
            (string) $row['source'],
            (string) $row['county'],
            (string) $row['country'],
            (string) $row['town'],
            (string) $row['description'],
            (string) $row['displayable_address'],
            $row['image_path'] !== null ? (string) $row['image_path'] : null,
            $row['thumbnail_path'] !== null ? (string) $row['thumbnail_path'] : null,
            $row['latitude'] !== null ? (float) $row['latitude'] : null,
            $row['longitude'] !== null ? (float) $row['longitude'] : null,
            (int) $row['bedrooms'],
            (int) $row['bathrooms'],
            (float) $row['price'],
            (string) $row['property_type'],
            (string) $row['listing_type'],
            $row['created_by'] !== null ? (int) $row['created_by'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toParams(Property $property): array
    {
        return array_merge(
            $this->toUpdateParams($property),
            [
                'source' => Property::SOURCE_MANUAL,
                'created_by' => $property->getCreatedBy(),
            ]
        );
    }

    /**
     * Named parameters for UPDATE only (must match the SET/WHERE placeholders in update()).
     *
     * @return array<string, mixed>
     */
    private function toUpdateParams(Property $property): array
    {
        return [
            'county' => $property->getCounty(),
            'country' => $property->getCountry(),
            'town' => $property->getTown(),
            'description' => $property->getDescription(),
            'displayable_address' => $property->getDisplayableAddress(),
            'image_path' => $property->getImagePath(),
            'thumbnail_path' => $property->getThumbnailPath(),
            'latitude' => $property->getLatitude(),
            'longitude' => $property->getLongitude(),
            'bedrooms' => $property->getBedrooms(),
            'bathrooms' => $property->getBathrooms(),
            'price' => $property->getPrice(),
            'property_type' => $property->getPropertyType(),
            'listing_type' => $property->getListingType(),
        ];
    }
}
