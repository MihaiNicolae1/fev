<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RecordPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Webadmin has full access to everything.
     *
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        // Webadmin bypasses all checks (superadmin pattern)
        if ($user->isWebadmin()) {
            return true;
        }

        return null; // Fall through to specific policy methods
    }

    /**
     * Determine whether the user can view any records.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermission(Permission::RECORDS_VIEW)
            ? Response::allow()
            : Response::deny('You do not have permission to view records.');
    }

    /**
     * Determine whether the user can view a specific record.
     *
     * @param User $user
     * @param Record $record
     * @return Response|bool
     */
    public function view(User $user, Record $record): Response|bool
    {
        // Can view all records
        if ($user->hasPermission(Permission::RECORDS_VIEW_ALL)) {
            return Response::allow();
        }

        // Can view records + owns this record
        if ($user->hasPermission(Permission::RECORDS_VIEW) && $record->created_by === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this record.');
    }

    /**
     * Determine whether the user can create records.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->hasPermission(Permission::RECORDS_CREATE)
            ? Response::allow()
            : Response::deny('You do not have permission to create records.');
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param User $user
     * @param Record $record
     * @return Response|bool
     */
    public function update(User $user, Record $record): Response|bool
    {
        // Can update any record
        if ($user->hasPermission(Permission::RECORDS_UPDATE)) {
            return Response::allow();
        }

        // Can update own records only
        if ($user->hasPermission(Permission::RECORDS_UPDATE_OWN) && $record->created_by === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this record.');
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param User $user
     * @param Record $record
     * @return Response|bool
     */
    public function delete(User $user, Record $record): Response|bool
    {
        // Can delete any record
        if ($user->hasPermission(Permission::RECORDS_DELETE)) {
            return Response::allow();
        }

        // Can delete own records only
        if ($user->hasPermission(Permission::RECORDS_DELETE_OWN) && $record->created_by === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to delete this record.');
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param User $user
     * @param Record $record
     * @return Response|bool
     */
    public function restore(User $user, Record $record): Response|bool
    {
        return $user->hasPermission(Permission::RECORDS_UPDATE)
            ? Response::allow()
            : Response::deny('You do not have permission to restore records.');
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param User $user
     * @param Record $record
     * @return Response|bool
     */
    public function forceDelete(User $user, Record $record): Response|bool
    {
        return $user->hasPermission(Permission::RECORDS_DELETE)
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete records.');
    }
}
