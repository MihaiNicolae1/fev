<?php

namespace App\Http\Pagination;

/**
 * Trait to add pagination capabilities to Form Request classes.
 * 
 * Usage in a FormRequest:
 *   use HasPagination;
 *   
 *   protected function paginationConfig(): array
 *   {
 *       return [
 *           'allowed_sort_fields' => ['id', 'name', 'created_at'],
 *           'default_sort_field' => 'id',
 *           'max_per_page' => 50,
 *       ];
 *   }
 */
trait HasPagination
{
    /**
     * Get pagination parameters from the request.
     */
    public function paginationParams(): PaginationParams
    {
        return PaginationParams::fromArray(
            $this->all(),
            $this->paginationConfig()
        );
    }

    /**
     * Override this method to customize pagination behavior.
     */
    protected function paginationConfig(): array
    {
        return [
            'max_per_page' => 100,
            'default_per_page' => 15,
            'default_sort_field' => 'id',
            'default_sort_order' => 'desc',
            'allowed_sort_fields' => ['id', 'created_at', 'updated_at'],
            'search_field' => 'search',
        ];
    }

    /**
     * Get pagination validation rules.
     * Merge these with your request's rules.
     */
    protected function paginationRules(): array
    {
        $config = $this->paginationConfig();
        $allowedFields = implode(',', $config['allowed_sort_fields'] ?? ['id', 'created_at', 'updated_at']);

        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . ($config['max_per_page'] ?? 100)],
            'sort_field' => ['sometimes', 'string', 'in:' . $allowedFields],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
