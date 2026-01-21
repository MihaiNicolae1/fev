<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dropdown_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'label',
        'value',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Dropdown types constants.
     */
    public const TYPE_SINGLE_SELECT = 'single_select';
    public const TYPE_MULTI_SELECT = 'multi_select';

    /**
     * Get records using this option as single select.
     */
    public function recordsAsSingleSelect()
    {
        return $this->hasMany(Record::class, 'single_select_id');
    }

    /**
     * Get record multi-options using this dropdown option.
     */
    public function recordMultiOptions()
    {
        return $this->hasMany(RecordMultiOption::class);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter active options only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get single select options.
     */
    public static function singleSelectOptions()
    {
        return static::ofType(self::TYPE_SINGLE_SELECT)->active()->get();
    }

    /**
     * Get multi select options.
     */
    public static function multiSelectOptions()
    {
        return static::ofType(self::TYPE_MULTI_SELECT)->active()->get();
    }
}
