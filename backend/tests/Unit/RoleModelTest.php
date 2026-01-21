<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test role has permissions.
     */
    public function test_role_has_permissions(): void
    {
        $role = Role::findBySlug(Role::WEBADMIN);

        $this->assertNotEmpty($role->permissions);
    }

    /**
     * Test hasPermission method.
     */
    public function test_has_permission_method(): void
    {
        $webadmin = Role::findBySlug(Role::WEBADMIN);
        $user = Role::findBySlug(Role::USER);

        $this->assertTrue($webadmin->hasPermission(Permission::RECORDS_CREATE));
        $this->assertFalse($user->hasPermission(Permission::RECORDS_CREATE));
    }

    /**
     * Test givePermissionTo method.
     */
    public function test_give_permission_to(): void
    {
        $user = Role::findBySlug(Role::USER);

        // User role doesn't have create permission initially
        $this->assertFalse($user->hasPermission(Permission::RECORDS_CREATE));

        // Give permission
        $user->givePermissionTo(Permission::RECORDS_CREATE);

        // Clear cache and check
        $user->refresh();
        $user->permissionsCache = null;

        $this->assertTrue($user->hasPermission(Permission::RECORDS_CREATE));
    }

    /**
     * Test revokePermissionTo method.
     */
    public function test_revoke_permission_to(): void
    {
        $user = Role::findBySlug(Role::USER);

        // User role has view permission initially
        $this->assertTrue($user->hasPermission(Permission::RECORDS_VIEW));

        // Revoke permission
        $user->revokePermissionTo(Permission::RECORDS_VIEW);

        // Clear cache and check
        $user->refresh();
        $user->permissionsCache = null;

        $this->assertFalse($user->hasPermission(Permission::RECORDS_VIEW));
    }

    /**
     * Test syncPermissions method.
     */
    public function test_sync_permissions(): void
    {
        $user = Role::findBySlug(Role::USER);

        // Sync with new permissions
        $user->syncPermissions([
            Permission::RECORDS_CREATE,
            Permission::RECORDS_UPDATE,
        ]);

        // Clear cache and check
        $user->refresh();
        $user->permissionsCache = null;

        $this->assertTrue($user->hasPermission(Permission::RECORDS_CREATE));
        $this->assertTrue($user->hasPermission(Permission::RECORDS_UPDATE));
        $this->assertFalse($user->hasPermission(Permission::RECORDS_VIEW)); // Was removed
    }

    /**
     * Test findBySlug static method.
     */
    public function test_find_by_slug(): void
    {
        $role = Role::findBySlug(Role::WEBADMIN);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('webadmin', $role->slug);
    }

    /**
     * Test bySlug scope.
     */
    public function test_by_slug_scope(): void
    {
        $role = Role::bySlug(Role::USER)->first();

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('user', $role->slug);
    }
}
