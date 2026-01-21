<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Records permissions
            [
                'name' => Permission::RECORDS_VIEW,
                'display_name' => 'View Records',
                'group' => 'records',
                'description' => 'Allows viewing records in the system',
            ],
            [
                'name' => Permission::RECORDS_VIEW_ALL,
                'display_name' => 'View All Records',
                'group' => 'records',
                'description' => 'Allows viewing all records, not just own records',
            ],
            [
                'name' => Permission::RECORDS_CREATE,
                'display_name' => 'Create Records',
                'group' => 'records',
                'description' => 'Allows creating new records',
            ],
            [
                'name' => Permission::RECORDS_UPDATE,
                'display_name' => 'Update Any Record',
                'group' => 'records',
                'description' => 'Allows updating any record in the system',
            ],
            [
                'name' => Permission::RECORDS_UPDATE_OWN,
                'display_name' => 'Update Own Records',
                'group' => 'records',
                'description' => 'Allows updating only records created by the user',
            ],
            [
                'name' => Permission::RECORDS_DELETE,
                'display_name' => 'Delete Any Record',
                'group' => 'records',
                'description' => 'Allows deleting any record in the system',
            ],
            [
                'name' => Permission::RECORDS_DELETE_OWN,
                'display_name' => 'Delete Own Records',
                'group' => 'records',
                'description' => 'Allows deleting only records created by the user',
            ],
            // Dropdown options permissions
            [
                'name' => Permission::DROPDOWN_OPTIONS_VIEW,
                'display_name' => 'View Dropdown Options',
                'group' => 'dropdown_options',
                'description' => 'Allows viewing dropdown options',
            ],
            [
                'name' => Permission::DROPDOWN_OPTIONS_MANAGE,
                'display_name' => 'Manage Dropdown Options',
                'group' => 'dropdown_options',
                'description' => 'Allows creating, updating, and deleting dropdown options',
            ],
            // User management permissions
            [
                'name' => Permission::USERS_VIEW,
                'display_name' => 'View Users',
                'group' => 'users',
                'description' => 'Allows viewing user information',
            ],
            [
                'name' => Permission::USERS_MANAGE,
                'display_name' => 'Manage Users',
                'group' => 'users',
                'description' => 'Allows creating, updating, and deleting users',
            ],
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles.
     *
     * @return void
     */
    protected function assignPermissionsToRoles(): void
    {
        // Webadmin gets ALL permissions (though they bypass checks anyway via before() hook)
        $webadmin = Role::findBySlug(Role::WEBADMIN);
        if ($webadmin) {
            $allPermissions = Permission::all()->pluck('name')->toArray();
            $webadmin->syncPermissions($allPermissions);
        }

        // Regular user gets limited permissions (read-only access)
        $user = Role::findBySlug(Role::USER);
        if ($user) {
            $userPermissions = [
                Permission::RECORDS_VIEW,
                Permission::RECORDS_VIEW_ALL,
                Permission::DROPDOWN_OPTIONS_VIEW,
            ];
            $user->syncPermissions($userPermissions);
        }
    }
}
