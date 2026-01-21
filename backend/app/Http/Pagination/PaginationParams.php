<?php

namespace App\Http\Pagination;

/**
 * Data Transfer Object for pagination parameters.
 * Provides type-safe access to pagination, sorting, and filtering parameters.
 */
class PaginationParams
{
    public int $page;
    public int $perPage;
    public ?string $sortField;
    public string $sortOrder;
    public ?string $search;
    public array $filters;

    public function __construct(
        int $page = 1,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortOrder = 'desc',
        ?string $search = null,
        array $filters = []
    ) {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->sortField = $sortField;
        $this->sortOrder = $sortOrder;
        $this->search = $search;
        $this->filters = $filters;
    }

    /**
     * Create from array (typically from request input).
     */
    public static function fromArray(array $data, array $config = []): self
    {
        $maxPerPage = $config['max_per_page'] ?? 100;
        $defaultPerPage = $config['default_per_page'] ?? 15;
        $defaultSortField = $config['default_sort_field'] ?? 'id';
        $defaultSortOrder = $config['default_sort_order'] ?? 'desc';
        $allowedSortFields = $config['allowed_sort_fields'] ?? ['id', 'created_at', 'updated_at'];
        $searchField = $config['search_field'] ?? 'search';

        $perPage = isset($data['per_page']) 
            ? min((int) $data['per_page'], $maxPerPage) 
            : $defaultPerPage;

        $sortField = $data['sort_field'] ?? $defaultSortField;
        
        // Validate sort field
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = $defaultSortField;
        }

        $sortOrder = strtolower($data['sort_order'] ?? $defaultSortOrder);
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = $defaultSortOrder;
        }

        return new self(
            page: max(1, (int) ($data['page'] ?? 1)),
            perPage: max(1, $perPage),
            sortField: $sortField,
            sortOrder: $sortOrder,
            search: $data[$searchField] ?? null,
            filters: $data['filters'] ?? [],
        );
    }

    /**
     * Check if search is active.
     */
    public function hasSearch(): bool
    {
        return !empty($this->search);
    }

    /**
     * Check if specific filters are set.
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    /**
     * Get a specific filter value.
     */
    public function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }
}
