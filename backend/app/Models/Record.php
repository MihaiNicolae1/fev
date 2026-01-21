<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'text_field',
        'single_select_id',
        'created_by',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['singleSelect', 'multiSelectOptions'];

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the single select option.
     */
    public function singleSelect()
    {
        return $this->belongsTo(DropdownOption::class, 'single_select_id');
    }

    /**
     * Get the multi-select options through the pivot table.
     */
    public function multiSelectOptions()
    {
        return $this->belongsToMany(
            DropdownOption::class,
            'record_multi_options',
            'record_id',
            'dropdown_option_id'
        );
    }

    /**
     * Sync multi-select options.
     *
     * @param array $optionIds
     * @return void
     */
    public function syncMultiSelectOptions(array $optionIds): void
    {
        $this->multiSelectOptions()->sync($optionIds);
    }

    /**
     * Get multi-select option IDs.
     *
     * @return array
     */
    public function getMultiSelectIds(): array
    {
        return $this->multiSelectOptions->pluck('id')->toArray();
    }
}
