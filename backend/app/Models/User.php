<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['role'];

    /**
     * Cache for permissions to avoid repeated queries.
     *
     * @var Collection|null
     */
    protected ?Collection $permissionsCache = null;

    /**
     * Get the role that the user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the records created by the user.
     */
    public function records(): HasMany
    {
        return $this->hasMany(Record::class, 'created_by');
    }

    /**
     * Get all permissions for this user through their role (cached).
     */
    public function getPermissions(): Collection
    {
        if ($this->permissionsCache === null) {
            $this->permissionsCache = $this->role?->getPermissionNames() ?? collect();
        }

        return $this->permissionsCache;
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->getPermissions()->contains($permission);
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->getPermissions()->intersect($permissions)->isNotEmpty();
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->getPermissions()->intersect($permissions)->count() === count($permissions);
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $roleSlug
     * @return bool
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Check if user is a webadmin.
     *
     * @return bool
     */
    public function isWebadmin(): bool
    {
        return $this->hasRole(Role::WEBADMIN);
    }

    /**
     * Check if user can perform an action (alias for hasPermission for Gate integration).
     *
     * @param string $ability
     * @return bool
     */
    public function can($ability, $arguments = []): bool
    {
        // If using Laravel's Gate system, delegate to parent
        if (!empty($arguments)) {
            return parent::can($ability, $arguments);
        }

        // Check direct permission
        return $this->hasPermission($ability);
    }

    /**
     * Clear permissions cache (useful after role change).
     */
    public function clearPermissionsCache(): void
    {
        $this->permissionsCache = null;
    }
}
