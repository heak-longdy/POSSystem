<?php

namespace Tests\Feature;

use App\Models\ModulePermission;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    /** @test */
    public function it_can_view_user_permission_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $targetUser = User::factory()->create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'role' => 'cashier',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin-user-permission', $targetUser->id));

        $response->assertStatus(200);
        $response->assertSee('Cashier User');
        $response->assertSee('Dashboard');
        $response->assertSee('Booking');
        $response->assertSee('Product');
        $response->assertViewHas('groupedModules');
        $response->assertViewHas('user');
        $response->assertViewHas('userPermissions');
    }

    /** @test */
    public function it_can_update_user_permissions()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $targetUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $permissionsToAssign = ['booking-view', 'booking-create', 'product-view'];

        $response = $this->post(route('admin-user-save-permission', $targetUser->id), [
            'id' => $targetUser->id,
            'permission' => $permissionsToAssign,
        ]);

        $response->assertRedirect(route('admin-user-list', 1));

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasPermissionTo('booking-view'));
        $this->assertTrue($targetUser->hasPermissionTo('booking-create'));
        $this->assertTrue($targetUser->hasPermissionTo('product-view'));
        $this->assertFalse($targetUser->hasPermissionTo('booking-delete'));
    }

    /** @test */
    public function it_can_revoke_all_permissions_when_submitting_empty()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $targetUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $targetUser->givePermissionTo(['booking-view', 'product-view']);
        $this->assertTrue($targetUser->hasPermissionTo('booking-view'));

        $this->actingAs($admin);

        $response = $this->post(route('admin-user-save-permission', $targetUser->id), [
            'id' => $targetUser->id,
            'permission' => [],
        ]);

        $response->assertRedirect(route('admin-user-list', 1));

        $targetUser->refresh();
        $this->assertFalse($targetUser->hasPermissionTo('booking-view'));
        $this->assertFalse($targetUser->hasPermissionTo('product-view'));
        $this->assertCount(0, $targetUser->permissions);
    }

    /** @test */
    public function it_safeguards_super_admin_from_permission_modification_by_non_super_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin-user-permission', $superAdmin->id));
        $response->assertRedirect(route('admin-user-list', 1));
        $response->assertSessionHas('warning');

        $postResponse = $this->post(route('admin-user-save-permission', $superAdmin->id), [
            'id' => $superAdmin->id,
            'permission' => ['dashboard-view'],
        ]);
        $postResponse->assertRedirect(route('admin-user-list', 1));
        $postResponse->assertSessionHas('warning');
    }

    /** @test */
    public function it_saves_user_with_role_and_syncs_spatie_role()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $payload = [
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'status' => 1,
            'role' => 'manager',
            'language_preference' => 'en',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
        ];

        $response = $this->post(route('admin-user-save'), $payload);
        $response->assertRedirect(route('admin-user-list', 1));

        $createdUser = User::where('email', 'manager@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('manager', $createdUser->role);
        $this->assertTrue($createdUser->hasRole('manager'));
    }
}
