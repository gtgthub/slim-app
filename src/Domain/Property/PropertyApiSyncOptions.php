<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertyApiSyncOptions
{
    private ?int $maxPages;

    private ?int $maxRecords;

    public function __construct(?int $maxPages, ?int $maxRecords)
    {
        $this->maxPages = $maxPages;
        $this->maxRecords = $maxRecords;
    }

    public function getMaxPages(): ?int
    {
        return $this->maxPages;
    }

    public function getMaxRecords(): ?int
    {
        return $this->maxRecords;
    }

    public function hasPageLimit(): bool
    {
        return $this->maxPages !== null;
    }

    public function hasRecordLimit(): bool
    {
        return $this->maxRecords !== null;
    }
}
