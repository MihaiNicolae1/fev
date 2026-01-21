<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRecordRequest;
use App\Http\Requests\UpdateRecordRequest;
use App\Http\Resources\RecordResource;
use App\Models\Record;
use App\Repositories\Contracts\RecordRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecordController extends BaseController
{
    /**
     * @var RecordRepositoryInterface
     */
    protected RecordRepositoryInterface $recordRepository;

    /**
     * RecordController constructor.
     *
     * @param RecordRepositoryInterface $recordRepository
     */
    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    /**
     * Display a listing of the records.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Authorize via policy
        $this->authorize('viewAny', Record::class);

        $perPage = $request->get('per_page', 15);
        $records = $this->recordRepository->getWithRelations($perPage);

        return $this->paginatedResponse(
            $records->through(fn($record) => new RecordResource($record)),
            'Records retrieved successfully'
        );
    }

    /**
     * Store a newly created record.
     *
     * @param StoreRecordRequest $request
     * @return JsonResponse
     */
    public function store(StoreRecordRequest $request): JsonResponse
    {
        // Authorize via policy
        $this->authorize('create', Record::class);

        $data = $request->validated();
        $multiSelectIds = $data['multi_select_ids'] ?? [];
        unset($data['multi_select_ids']);

        // Set created_by to current user
        $data['created_by'] = $request->user()->id;

        $record = $this->recordRepository->createWithMultiSelect($data, $multiSelectIds);

        return $this->createdResponse(
            new RecordResource($record),
            'Record created successfully'
        );
    }

    /**
     * Display the specified record.
     *
     * @param Record $record
     * @return JsonResponse
     */
    public function show(Record $record): JsonResponse
    {
        // Authorize via policy
        $this->authorize('view', $record);

        $record->load(['singleSelect', 'multiSelectOptions', 'creator']);

        return $this->successResponse(
            new RecordResource($record),
            'Record retrieved successfully'
        );
    }

    /**
     * Update the specified record.
     *
     * @param UpdateRecordRequest $request
     * @param Record $record
     * @return JsonResponse
     */
    public function update(UpdateRecordRequest $request, Record $record): JsonResponse
    {
        // Authorize via policy
        $this->authorize('update', $record);

        $data = $request->validated();
        $multiSelectIds = $data['multi_select_ids'] ?? [];
        unset($data['multi_select_ids']);

        $record = $this->recordRepository->updateWithMultiSelect(
            $record->id,
            $data,
            $multiSelectIds
        );

        return $this->successResponse(
            new RecordResource($record),
            'Record updated successfully'
        );
    }

    /**
     * Remove the specified record.
     *
     * @param Record $record
     * @return JsonResponse
     */
    public function destroy(Record $record): JsonResponse
    {
        // Authorize via policy
        $this->authorize('delete', $record);

        $this->recordRepository->delete($record->id);

        return $this->successResponse(null, 'Record deleted successfully');
    }
}
