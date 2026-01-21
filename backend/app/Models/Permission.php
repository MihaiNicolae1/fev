<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
    ];

    /**
     * Permission name constants for type safety.
     */
    public const RECORDS_VIEW = 'records.view';
    public const RECORDS_VIEW_ALL = 'records.view_all';
    public const RECORDS_CREATE = 'records.create';
    public const RECORDS_UPDATE = 'records.update';
    public const RECORDS_UPDATE_OWN = 'records.update_own';
    public const RECORDS_DELETE = 'records.delete';
    public const RECORDS_DELETE_OWN = 'records.delete_own';

    public const DROPDOWN_OPTIONS_VIEW = 'dropdown_options.view';
    public const DROPDOWN_OPTIONS_MANAGE = 'dropdown_options.manage';

    public const USERS_VIEW = 'users.view';
    public const USERS_MANAGE = 'users.manage';

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Scope to filter by group.
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get all permission groups.
     */
    public static function getGroups(): array
    {
        return static::distinct()->pluck('group')->toArray();
    }
}
