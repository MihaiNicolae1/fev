<?php

namespace App\Http\Requests;

/**
 * Request for listing records with pagination.
 * Automatically validates and extracts pagination parameters.
 */
class IndexRecordsRequest extends PaginatedRequest
{
    /**
     * Configure pagination for records.
     */
    protected function paginationConfig(): array
    {
        return [
            'max_per_page' => 100,
            'default_per_page' => 15,
            'default_sort_field' => 'id',
            'default_sort_order' => 'desc',
            'allowed_sort_fields' => ['id', 'text_field', 'created_at', 'updated_at'],
            'search_field' => 'search',
        ];
    }
}
