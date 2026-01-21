<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user belongs to a role.
     */
    public function test_user_belongs_to_role(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Role::class, $user->role);
    }

    /**
     * Test user has permissions through role.
     */
    public function test_user_has_permissions_through_role(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::findBySlug(Role::WEBADMIN)->id,
        ]);

        $permissions = $user->getPermissions();

        $this->assertNotEmpty($permissions);
        $this->assertTrue($permissions->contains(Permission::RECORDS_VIEW));
    }

    /**
     * Test hasPermission method.
     */
    public function test_has_permission_method(): void
    {
        $webadmin = User::factory()->create([
            'role_id' => Role::findBySlug(Role::WEBADMIN)->id,
        ]);

        $regularUser = User::factory()->create([
            'role_id' => Role::findBySlug(Role::USER)->id,
        ]);

        // Webadmin has create permission
        $this->assertTrue($webadmin->hasPermission(Permission::RECORDS_CREATE));

        // Regular user does not have create permission
        $this->assertFalse($regularUser->hasPermission(Permission::RECORDS_CREATE));
    }

    /**
     * Test hasAnyPermission method.
     */
    public function test_has_any_permission_method(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::findBySlug(Role::USER)->id,
        ]);

        // User has view permission
        $this->assertTrue($user->hasAnyPermission([
            Permission::RECORDS_VIEW,
            Permission::RECORDS_CREATE,
        ]));

        // User has neither of these
        $this->assertFalse($user->hasAnyPermission([
            Permission::RECORDS_CREATE,
            Permission::RECORDS_DELETE,
        ]));
    }

    /**
     * Test isWebadmin method.
     */
    public function test_is_webadmin_method(): void
    {
        $webadmin = User::factory()->create([
            'role_id' => Role::findBySlug(Role::WEBADMIN)->id,
        ]);

        $regularUser = User::factory()->create([
            'role_id' => Role::findBySlug(Role::USER)->id,
        ]);

        $this->assertTrue($webadmin->isWebadmin());
        $this->assertFalse($regularUser->isWebadmin());
    }

    /**
     * Test hasRole method.
     */
    public function test_has_role_method(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::findBySlug(Role::USER)->id,
        ]);

        $this->assertTrue($user->hasRole(Role::USER));
        $this->assertFalse($user->hasRole(Role::WEBADMIN));
    }
}
