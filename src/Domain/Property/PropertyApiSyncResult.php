<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertyApiSyncResult
{
    private int $pagesFetched;

    private int $recordsProcessed;

    private int $created;

    private int $updated;

    private int $skipped;

    private int $errors;

    private string $stoppedReason;

    public function __construct(
        int $pagesFetched,
        int $recordsProcessed,
        int $created,
        int $updated,
        int $skipped,
        int $errors,
        string $stoppedReason
    ) {
        $this->pagesFetched = $pagesFetched;
        $this->recordsProcessed = $recordsProcessed;
        $this->created = $created;
        $this->updated = $updated;
        $this->skipped = $skipped;
        $this->errors = $errors;
        $this->stoppedReason = $stoppedReason;
    }

    public function getPagesFetched(): int
    {
        return $this->pagesFetched;
    }

    public function getRecordsProcessed(): int
    {
        return $this->recordsProcessed;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): int
    {
        return $this->errors;
    }

    public function getStoppedReason(): string
    {
        return $this->stoppedReason;
    }
}
