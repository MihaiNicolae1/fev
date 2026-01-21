<?php

namespace App\Repositories\Contracts;

use App\Models\Record;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RecordRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get records with relationships.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithRelations(int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a record with multi-select options.
     *
     * @param array $data
     * @param array $multiSelectIds
     * @return Record
     */
    public function createWithMultiSelect(array $data, array $multiSelectIds = []): Record;

    /**
     * Update a record with multi-select options.
     *
     * @param int $id
     * @param array $data
     * @param array $multiSelectIds
     * @return Record
     */
    public function updateWithMultiSelect(int $id, array $data, array $multiSelectIds = []): Record;
}
