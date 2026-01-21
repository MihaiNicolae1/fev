<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DropdownOptionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get options by type.
     *
     * @param string $type
     * @return Collection
     */
    public function getByType(string $type): Collection;

    /**
     * Get all active options.
     *
     * @return Collection
     */
    public function getActiveOptions(): Collection;

    /**
     * Get options grouped by type.
     *
     * @return array
     */
    public function getGroupedByType(): array;
}
