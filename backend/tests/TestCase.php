<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected bool $seed = false;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for testing
        Artisan::call('migrate:fresh');

        // Install Passport (creates personal access client)
        Artisan::call('passport:install', ['--no-interaction' => true]);

        // Seed required data
        $this->seedRolesAndPermissions();
    }

    /**
     * Seed roles and permissions for testing.
     *
     * @return void
     */
    protected function seedRolesAndPermissions(): void
    {
        // Create roles
        $webadminRole = Role::create([
            'name' => 'Web Administrator',
            'slug' => Role::WEBADMIN,
        ]);

        $userRole = Role::create([
            'name' => 'User',
            'slug' => Role::USER,
        ]);

        // Create permissions
        $permissions = [
            ['name' => Permission::RECORDS_VIEW, 'display_name' => 'View Records', 'group' => 'records'],
            ['name' => Permission::RECORDS_VIEW_ALL, 'display_name' => 'View All Records', 'group' => 'records'],
            ['name' => Permission::RECORDS_CREATE, 'display_name' => 'Create Records', 'group' => 'records'],
            ['name' => Permission::RECORDS_UPDATE, 'display_name' => 'Update Any Record', 'group' => 'records'],
            ['name' => Permission::RECORDS_UPDATE_OWN, 'display_name' => 'Update Own Records', 'group' => 'records'],
            ['name' => Permission::RECORDS_DELETE, 'display_name' => 'Delete Any Record', 'group' => 'records'],
            ['name' => Permission::RECORDS_DELETE_OWN, 'display_name' => 'Delete Own Records', 'group' => 'records'],
            ['name' => Permission::DROPDOWN_OPTIONS_VIEW, 'display_name' => 'View Dropdown Options', 'group' => 'dropdown_options'],
            ['name' => Permission::DROPDOWN_OPTIONS_MANAGE, 'display_name' => 'Manage Dropdown Options', 'group' => 'dropdown_options'],
            ['name' => Permission::USERS_VIEW, 'display_name' => 'View Users', 'group' => 'users'],
            ['name' => Permission::USERS_MANAGE, 'display_name' => 'Manage Users', 'group' => 'users'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign all permissions to webadmin
        $webadminRole->syncPermissions(Permission::all()->pluck('name')->toArray());

        // Assign limited permissions to user role
        $userRole->syncPermissions([
            Permission::RECORDS_VIEW,
            Permission::RECORDS_VIEW_ALL,
            Permission::DROPDOWN_OPTIONS_VIEW,
        ]);
    }

    /**
     * Create and authenticate as a webadmin user.
     *
     * @return User
     */
    protected function actingAsWebadmin(): User
    {
        $user = User::factory()->create([
            'role_id' => Role::findBySlug(Role::WEBADMIN)->id,
        ]);

        Passport::actingAs($user);

        return $user;
    }

    /**
     * Create and authenticate as a regular user.
     *
     * @return User
     */
    protected function actingAsUser(): User
    {
        $user = User::factory()->create([
            'role_id' => Role::findBySlug(Role::USER)->id,
        ]);

        Passport::actingAs($user);

        return $user;
    }

    /**
     * Create an unauthenticated user (for testing auth endpoints).
     *
     * @param string $roleSlug
     * @return User
     */
    protected function createUser(string $roleSlug = Role::USER): User
    {
        return User::factory()->create([
            'role_id' => Role::findBySlug($roleSlug)->id,
        ]);
    }
}
