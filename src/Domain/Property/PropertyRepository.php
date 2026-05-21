<?php

declare(strict_types=1);

namespace App\Domain\Property;

interface PropertyRepository
{
    /**
     * @return Property[]
     */
    public function findAllManual(): array;

    public function findManualById(int $id): Property;

    public function create(Property $property): Property;

    public function update(Property $property): Property;

    public function softDelete(int $id): void;

    /** Remove a row permanently (e.g. rollback after failed create). */
    public function forceDelete(int $id): void;
}
