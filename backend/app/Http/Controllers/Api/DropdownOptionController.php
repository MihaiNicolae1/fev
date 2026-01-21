<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDropdownOptionRequest;
use App\Http\Requests\UpdateDropdownOptionRequest;
use App\Http\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use App\Repositories\Contracts\DropdownOptionRepositoryInterface;
use Illuminate\Http\JsonResponse;

class DropdownOptionController extends BaseController
{
    /**
     * @var DropdownOptionRepositoryInterface
     */
    protected DropdownOptionRepositoryInterface $dropdownOptionRepository;

    /**
     * DropdownOptionController constructor.
     *
     * @param DropdownOptionRepositoryInterface $dropdownOptionRepository
     */
    public function __construct(DropdownOptionRepositoryInterface $dropdownOptionRepository)
    {
        $this->dropdownOptionRepository = $dropdownOptionRepository;
    }

    /**
     * Display a listing of all dropdown options grouped by type.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $grouped = $this->dropdownOptionRepository->getGroupedByType();

        return $this->successResponse([
            'single_select' => DropdownOptionResource::collection($grouped['single_select']),
            'multi_select' => DropdownOptionResource::collection($grouped['multi_select']),
        ], 'Dropdown options retrieved successfully');
    }

    /**
     * Display dropdown options by type.
     *
     * @param string $type
     * @return JsonResponse
     */
    public function byType(string $type): JsonResponse
    {
        if (!in_array($type, [DropdownOption::TYPE_SINGLE_SELECT, DropdownOption::TYPE_MULTI_SELECT])) {
            return $this->errorResponse('Invalid dropdown type', 400);
        }

        $options = $this->dropdownOptionRepository->getByType($type);

        return $this->successResponse(
            DropdownOptionResource::collection($options),
            'Dropdown options retrieved successfully'
        );
    }

    /**
     * Store a newly created dropdown option.
     *
     * @param StoreDropdownOptionRequest $request
     * @return JsonResponse
     */
    public function store(StoreDropdownOptionRequest $request): JsonResponse
    {
        $option = $this->dropdownOptionRepository->create($request->validated());

        return $this->createdResponse(
            new DropdownOptionResource($option),
            'Dropdown option created successfully'
        );
    }

    /**
     * Update the specified dropdown option.
     *
     * @param UpdateDropdownOptionRequest $request
     * @param DropdownOption $dropdownOption
     * @return JsonResponse
     */
    public function update(UpdateDropdownOptionRequest $request, DropdownOption $dropdownOption): JsonResponse
    {
        $option = $this->dropdownOptionRepository->update($dropdownOption->id, $request->validated());

        return $this->successResponse(
            new DropdownOptionResource($option),
            'Dropdown option updated successfully'
        );
    }

    /**
     * Remove the specified dropdown option.
     *
     * @param DropdownOption $dropdownOption
     * @return JsonResponse
     */
    public function destroy(DropdownOption $dropdownOption): JsonResponse
    {
        $this->dropdownOptionRepository->delete($dropdownOption->id);

        return $this->successResponse(null, 'Dropdown option deleted successfully');
    }
}
