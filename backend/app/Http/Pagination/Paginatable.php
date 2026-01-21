<?php

namespace App\Http\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Trait to add pagination capabilities to controllers or repositories.
 * 
 * Usage in a Controller or Repository:
 *   use Paginatable;
 *   
 *   public function index(IndexRecordsRequest $request)
 *   {
 *       $query = Record::query()->with(['relation']);
 *       $paginator = $this->paginate($query, $request->paginationParams(), ['text_field']);
 *       return $this->paginatedResponse($paginator);
 *   }
 */
trait Paginatable
{
    /**
     * Apply pagination, sorting, and search to an Eloquent query.
     *
     * @param Builder $query The Eloquent query builder
     * @param PaginationParams $params Pagination parameters
     * @param array $searchableFields Fields to search in (for LIKE queries)
     * @return LengthAwarePaginator
     */
    protected function paginate(
        Builder $query,
        PaginationParams $params,
        array $searchableFields = []
    ): LengthAwarePaginator {
        // Apply search
        if ($params->hasSearch() && !empty($searchableFields)) {
            $query->where(function (Builder $q) use ($params, $searchableFields) {
                foreach ($searchableFields as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $q->{$method}($field, 'like', '%' . $params->search . '%');
                }
            });
        }

        // Apply sorting
        if ($params->sortField) {
            $query->orderBy($params->sortField, $params->sortOrder);
        }

        // Apply pagination
        return $query->paginate(
            perPage: $params->perPage,
            page: $params->page
        );
    }

    /**
     * Apply pagination to a query with custom search logic.
     *
     * @param Builder $query The Eloquent query builder
     * @param PaginationParams $params Pagination parameters
     * @param callable|null $searchCallback Custom search callback
     * @return LengthAwarePaginator
     */
    protected function paginateWithCustomSearch(
        Builder $query,
        PaginationParams $params,
        ?callable $searchCallback = null
    ): LengthAwarePaginator {
        // Apply custom search
        if ($params->hasSearch() && $searchCallback !== null) {
            $searchCallback($query, $params->search);
        }

        // Apply sorting
        if ($params->sortField) {
            $query->orderBy($params->sortField, $params->sortOrder);
        }

        // Apply pagination
        return $query->paginate(
            perPage: $params->perPage,
            page: $params->page
        );
    }
}
