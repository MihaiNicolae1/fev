<?php

namespace App\Repositories\Eloquent;

use App\Models\DropdownOption;
use App\Repositories\Contracts\DropdownOptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DropdownOptionRepository extends BaseRepository implements DropdownOptionRepositoryInterface
{
    /**
     * DropdownOptionRepository constructor.
     *
     * @param DropdownOption $model
     */
    public function __construct(DropdownOption $model)
    {
        parent::__construct($model);
    }

    /**
     * Get options by type.
     *
     * @param string $type
     * @return Collection
     */
    public function getByType(string $type): Collection
    {
        return $this->model
            ->ofType($type)
            ->active()
            ->orderBy('label')
            ->get();
    }

    /**
     * Get all active options.
     *
     * @return Collection
     */
    public function getActiveOptions(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('type')
            ->orderBy('label')
            ->get();
    }

    /**
     * Get options grouped by type.
     *
     * @return array
     */
    public function getGroupedByType(): array
    {
        $options = $this->getActiveOptions();

        return [
            'single_select' => $options->where('type', DropdownOption::TYPE_SINGLE_SELECT)->values(),
            'multi_select' => $options->where('type', DropdownOption::TYPE_MULTI_SELECT)->values(),
        ];
    }
}
