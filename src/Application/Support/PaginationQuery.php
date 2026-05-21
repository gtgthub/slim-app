<?php

declare(strict_types=1);

namespace App\Application\Support;

final class PaginationQuery
{
    public const DEFAULT_PER_PAGE = 10;

    /** @var int[] */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private int $page;

    private int $perPage;

    private function __construct(int $page, int $perPage)
    {
        $this->page = $page;
        $this->perPage = $perPage;
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public static function fromQueryParams(array $queryParams): self
    {
        $perPage = isset($queryParams['per_page']) ? (int) $queryParams['per_page'] : self::DEFAULT_PER_PAGE;
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        return new self($page, $perPage);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function totalPages(int $totalItems): int
    {
        if ($totalItems <= 0) {
            return 0;
        }

        return (int) ceil($totalItems / $this->perPage);
    }

    public function clampPage(int $totalItems): int
    {
        $totalPages = $this->totalPages($totalItems);
        if ($totalPages === 0) {
            return 1;
        }

        return min($this->page, $totalPages);
    }

    public function buildUrl(int $page, ?int $perPage = null): string
    {
        $perPage = $perPage ?? $this->perPage;

        return '/admin/properties?' . http_build_query([
            'page' => max(1, $page),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Page numbers to render, including gaps as null (ellipsis).
     *
     * @return array<int|null>
     */
    public function pageNumberWindow(int $totalPages, int $currentPage): array
    {
        if ($totalPages <= 0) {
            return [];
        }

        if ($totalPages <= 7) {
            return range(1, $totalPages);
        }

        $window = [1];
        $innerStart = max(2, $currentPage - 1);
        $innerEnd = min($totalPages - 1, $currentPage + 1);

        if ($innerStart > 2) {
            $window[] = null;
        }

        for ($p = $innerStart; $p <= $innerEnd; $p++) {
            $window[] = $p;
        }

        if ($innerEnd < $totalPages - 1) {
            $window[] = null;
        }

        $window[] = $totalPages;

        return $window;
    }
}
