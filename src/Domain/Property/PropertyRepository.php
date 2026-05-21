<?php

declare(strict_types=1);

namespace App\Domain\Property;

interface PropertyRepository
{
    /**
     * @return Property[]
     */
    public function findAll(): array;

    public function countAll(): int;

    /**
     * @return Property[]
     */
    public function findPaginated(int $page, int $perPage): array;

    /**
     * @return Property[]
     */
    public function findAllManual(): array;

    public function findById(int $id): Property;

    public function findManualById(int $id): Property;

    public function create(Property $property): Property;

    public function update(Property $property): Property;

    public function softDelete(int $id): void;

    /** Remove a row permanently (e.g. rollback after failed create). */
    public function forceDelete(int $id): void;

    public function findApiByUuid(string $uuid): ?Property;

    public function upsertApi(Property $property): Property;

    public function countBySearch(PropertySearchCriteria $criteria): int;

    /**
     * @return Property[]
     */
    public function findBySearch(PropertySearchCriteria $criteria, int $page, int $perPage): array;

    /**
     * @return string[]
     */
    public function findDisplayableAddressesLike(string $term, int $limit): array;
}
