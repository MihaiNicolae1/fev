<?php

namespace App\Repositories\Eloquent;

use App\Models\Record;
use App\Repositories\Contracts\RecordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecordRepository extends BaseRepository implements RecordRepositoryInterface
{
    /**
     * RecordRepository constructor.
     *
     * @param Record $model
     */
    public function __construct(Record $model)
    {
        parent::__construct($model);
    }

    /**
     * Get records with relationships.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithRelations(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['singleSelect', 'multiSelectOptions', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a record with multi-select options.
     *
     * @param array $data
     * @param array $multiSelectIds
     * @return Record
     */
    public function createWithMultiSelect(array $data, array $multiSelectIds = []): Record
    {
        $record = $this->create($data);

        if (!empty($multiSelectIds)) {
            $record->syncMultiSelectOptions($multiSelectIds);
        }

        return $record->fresh(['singleSelect', 'multiSelectOptions']);
    }

    /**
     * Update a record with multi-select options.
     *
     * @param int $id
     * @param array $data
     * @param array $multiSelectIds
     * @return Record
     */
    public function updateWithMultiSelect(int $id, array $data, array $multiSelectIds = []): Record
    {
        $record = $this->update($id, $data);
        $record->syncMultiSelectOptions($multiSelectIds);

        return $record->fresh(['singleSelect', 'multiSelectOptions']);
    }
}
