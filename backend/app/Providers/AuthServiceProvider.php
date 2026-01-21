<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Record;
use App\Models\User;
use App\Policies\RecordPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Record::class => RecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGates();

        // Passport token expiration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }

    /**
     * Register application Gates for permission-based authorization.
     *
     * @return void
     */
    protected function registerGates(): void
    {
        // Super admin gate - webadmin can do anything
        Gate::before(function (User $user, string $ability) {
            if ($user->isWebadmin()) {
                return true;
            }
            return null;
        });

        // Register gates for each permission constant
        $this->registerPermissionGates();
    }

    /**
     * Register gates based on Permission constants.
     *
     * @return void
     */
    protected function registerPermissionGates(): void
    {
        // Records permissions
        Gate::define(Permission::RECORDS_VIEW, fn(User $user) => $user->hasPermission(Permission::RECORDS_VIEW));
        Gate::define(Permission::RECORDS_VIEW_ALL, fn(User $user) => $user->hasPermission(Permission::RECORDS_VIEW_ALL));
        Gate::define(Permission::RECORDS_CREATE, fn(User $user) => $user->hasPermission(Permission::RECORDS_CREATE));
        Gate::define(Permission::RECORDS_UPDATE, fn(User $user) => $user->hasPermission(Permission::RECORDS_UPDATE));
        Gate::define(Permission::RECORDS_UPDATE_OWN, fn(User $user) => $user->hasPermission(Permission::RECORDS_UPDATE_OWN));
        Gate::define(Permission::RECORDS_DELETE, fn(User $user) => $user->hasPermission(Permission::RECORDS_DELETE));
        Gate::define(Permission::RECORDS_DELETE_OWN, fn(User $user) => $user->hasPermission(Permission::RECORDS_DELETE_OWN));

        // Dropdown options permissions
        Gate::define(Permission::DROPDOWN_OPTIONS_VIEW, fn(User $user) => $user->hasPermission(Permission::DROPDOWN_OPTIONS_VIEW));
        Gate::define(Permission::DROPDOWN_OPTIONS_MANAGE, fn(User $user) => $user->hasPermission(Permission::DROPDOWN_OPTIONS_MANAGE));

        // Users permissions
        Gate::define(Permission::USERS_VIEW, fn(User $user) => $user->hasPermission(Permission::USERS_VIEW));
        Gate::define(Permission::USERS_MANAGE, fn(User $user) => $user->hasPermission(Permission::USERS_MANAGE));
    }
}
