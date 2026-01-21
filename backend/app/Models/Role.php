<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Role extends Model
{
    use HasFactory;

    /**
     * Role slug constants.
     */
    public const WEBADMIN = 'webadmin';
    public const USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Cache for permissions to avoid repeated queries.
     *
     * @var Collection|null
     */
    protected ?Collection $permissionsCache = null;

    /**
     * Get the users that belong to the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the permissions assigned to this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Get all permission names for this role (cached).
     */
    public function getPermissionNames(): Collection
    {
        if ($this->permissionsCache === null) {
            $this->permissionsCache = $this->permissions()->pluck('name');
        }

        return $this->permissionsCache;
    }

    /**
     * Check if role has a specific permission.
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->getPermissionNames()->contains($permissionName);
    }

    /**
     * Check if role has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->getPermissionNames()->intersect($permissions)->isNotEmpty();
    }

    /**
     * Check if role has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->getPermissionNames()->intersect($permissions)->count() === count($permissions);
    }

    /**
     * Assign permission(s) to role.
     *
     * @param string|array $permissions
     * @return void
     */
    public function givePermissionTo(string|array $permissions): void
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');

        $this->permissions()->syncWithoutDetaching($permissionIds);
        $this->permissionsCache = null; // Clear cache
    }

    /**
     * Revoke permission(s) from role.
     *
     * @param string|array $permissions
     * @return void
     */
    public function revokePermissionTo(string|array $permissions): void
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');

        $this->permissions()->detach($permissionIds);
        $this->permissionsCache = null; // Clear cache
    }

    /**
     * Sync permissions for role.
     *
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');

        $this->permissions()->sync($permissionIds);
        $this->permissionsCache = null; // Clear cache
    }

    /**
     * Scope to find a role by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Find role by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::bySlug($slug)->first();
    }
}
