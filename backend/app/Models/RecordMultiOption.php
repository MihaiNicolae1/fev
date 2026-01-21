<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordMultiOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'record_multi_options';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'record_id',
        'dropdown_option_id',
    ];

    /**
     * Get the record.
     */
    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Get the dropdown option.
     */
    public function dropdownOption()
    {
        return $this->belongsTo(DropdownOption::class);
    }
}
