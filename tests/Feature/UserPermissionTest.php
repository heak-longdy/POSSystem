<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ModulePermission;
use App\Support\PermissionModuleSelection;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('language_preference')->default('en');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        (new PermissionSeeder())->run();
        $this->manager = User::create(['name' => 'Manager', 'email' => 'manager@example.test', 'role' => 'admin']);
        $this->manager->givePermissionTo(['user-view', 'user-update']);
        $this->staff = User::create(['name' => 'Staff', 'email' => 'staff@example.test', 'role' => 'staff']);
    }

    private function saveUrl(?User $user = null): string
    {
        return route('admin-user-save-permission', ($user ?? $this->staff)->id);
    }

    public function test_assignment_reload_replacement_and_revocation_apply_to_access(): void
    {
        $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))
            ->assertOk()->assertSee('permission[]', false)->assertViewHas('userPermissions', []);
        $this->post($this->saveUrl(), ['permission' => ['user-view', 'product-view']])
            ->assertRedirect(route('admin-user-list', 1))->assertSessionHas('success');
        $this->get(route('admin-user-permission', $this->staff->id))
            ->assertViewHas('userPermissions', fn ($names) => count($names) === 2 && in_array('product-view', $names));
        $this->actingAs($this->staff->fresh())->get(route('admin-user-list', 1))->assertOk()
            ->assertDontSee('href="' . route('admin-user-permission', $this->manager->id) . '"', false)
            ->assertDontSee('s-click-link="' . route('admin-user-create') . '"', false);
        $this->getJson(route('admin-user-permission', $this->manager->id))->assertForbidden();

        $this->actingAs($this->manager)->postJson($this->saveUrl(), ['permission' => ['user-update']])
            ->assertOk()->assertJson(['error' => false]);
        $this->assertSame(['user-update'], $this->staff->fresh()->permissions->pluck('name')->all());
        $this->actingAs($this->staff->fresh())->getJson(route('admin-user-list', 1))->assertForbidden();
        $this->actingAs($this->manager)->postJson($this->saveUrl(), [])->assertOk();
        $this->assertCount(0, $this->staff->fresh()->permissions);
        $this->assertFalse(Gate::forUser($this->staff->fresh())->allows('user-update'));
    }

    public function test_explicit_empty_array_revokes_all_permissions(): void
    {
        $this->staff->givePermissionTo('product-view');
        $this->actingAs($this->manager)->postJson($this->saveUrl(), ['permission' => []])->assertOk();
        $this->assertCount(0, $this->staff->fresh()->permissions);
    }

    public function test_new_user_can_be_created_assigned_permissions_and_edited_without_losing_access(): void
    {
        $this->manager->givePermissionTo('user-create');
        $this->actingAs($this->manager)->post(route('admin-user-save'), [
            'name' => 'New cashier', 'email' => 'cashier@example.test', 'status' => 1,
            'role' => 'cashier', 'language_preference' => 'en',
            'password' => 'test-password', 'confirm_password' => 'test-password',
        ])->assertRedirect(route('admin-user-list', 1));
        $user = User::where('email', 'cashier@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('cashier'));
        $this->postJson($this->saveUrl($user), ['permission' => ['user-view']])->assertOk();
        $this->post(route('admin-user-save', $user->id), [
            'name' => 'Updated cashier', 'email' => $user->email, 'status' => 1,
            'role' => 'cashier', 'language_preference' => 'en',
        ])->assertRedirect(route('admin-user-list', 1));
        $this->assertSame('Updated cashier', $user->fresh()->name);
        $this->actingAs($user->fresh())->get(route('admin-user-list', 1))->assertOk();
        $this->getJson(route('admin-user-create'))->assertForbidden();
    }

    public function test_invalid_permissions_never_clear_existing_assignments(): void
    {
        Permission::create(['name' => 'api-only', 'guard_name' => 'api']);
        $this->staff->givePermissionTo('product-view');
        $this->actingAs($this->manager);
        foreach (['product-view', null, ['missing'], ['api-only'], [1], [['name' => 'product-view']], ['product-view', 'product-view']] as $invalid) {
            $this->postJson($this->saveUrl(), ['permission' => $invalid])->assertUnprocessable();
            $this->assertSame(['product-view'], $this->staff->fresh()->permissions->pluck('name')->all());
        }
        $this->from(route('admin-user-permission', $this->staff->id))
            ->post($this->saveUrl(), ['_permissions_form' => '1', 'permission' => ['missing']])
            ->assertSessionHasErrors('permission.0')->assertSessionHasInput('permission', ['missing']);
    }

    public function test_guests_readers_and_other_users_cannot_change_permissions(): void
    {
        $this->post($this->saveUrl(), [])->assertRedirect();
        $this->staff->givePermissionTo('user-view');
        $this->actingAs($this->staff)->getJson(route('admin-user-permission', $this->manager->id))->assertForbidden();
        $this->postJson($this->saveUrl(), ['permission' => ['user-update']])->assertForbidden();
        $this->assertSame(['user-view'], $this->staff->fresh()->permissions->pluck('name')->all());
        $this->actingAs($this->manager)->postJson(route('admin-user-save-permission', 99999), [])->assertNotFound();
        $this->staff->delete();
        $this->postJson($this->saveUrl(), [])->assertNotFound();
    }

    public function test_route_target_takes_precedence_over_a_forged_body_id(): void
    {
        $this->actingAs($this->manager)->postJson($this->saveUrl(), [
            'id' => $this->manager->id, 'permission' => ['product-view'],
        ])->assertOk();
        $this->assertTrue($this->staff->fresh()->can('product-view'));
        $this->assertFalse($this->manager->fresh()->can('product-view'));
    }

    public function test_role_permissions_are_applied_and_explained_without_changing_the_role(): void
    {
        $role = Role::create(['name' => 'cashier', 'guard_name' => 'web']);
        $role->givePermissionTo('user-view');
        $this->staff->assignRole($role);
        $this->staff->givePermissionTo('product-view');
        $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))
            ->assertOk()->assertSee(__('permission.inherited_help'))
            ->assertViewHas('inheritedPermissions', ['user-view']);
        $this->postJson($this->saveUrl(), [])->assertOk();
        $staff = $this->staff->fresh();
        $this->assertTrue($staff->can('user-view'));
        $this->assertFalse($staff->can('product-view'));
        $this->assertTrue($staff->hasRole('cashier'));
        $this->actingAs($staff)->get(route('admin-user-list', 1))->assertOk();
    }

    public function test_super_admin_bypass_is_preserved_and_cannot_be_assigned_through_user_editing(): void
    {
        $super = User::create(['name' => 'Super', 'email' => 'super@example.test', 'role' => 'super_admin']);
        $this->assertTrue($super->can('product-update'));
        $this->actingAs($this->manager)->getJson(route('admin-user-permission', $super->id))->assertForbidden();
        $this->postJson($this->saveUrl($super), [])->assertForbidden();
        $this->postJson(route('admin-user-save', $this->staff->id), ['role' => 'super_admin'])->assertForbidden();
        $this->postJson(route('admin-user-save-password', $this->staff->id), ['id' => $super->id])->assertForbidden();
        $this->actingAs($super)->get(route('admin-user-permission', $this->staff->id))->assertOk();
    }

    public function test_catalog_can_be_rerun_and_migrated_without_changing_ids_or_assignments(): void
    {
        $this->staff->givePermissionTo('product-view');
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('user-view');
        $before = DB::table('permissions')->pluck('id', 'name')->all();
        $moduleIds = DB::table('module_permissions')->pluck('id', 'name')->all();
        (new PermissionSeeder())->run();
        $migration = require database_path('migrations/2026_10_15_000000_complete_user_permission_catalog.php');
        $migration->up();
        $this->assertSame($before, DB::table('permissions')->pluck('id', 'name')->all());
        $this->assertSame($moduleIds, DB::table('module_permissions')->pluck('id', 'name')->all());
        $this->assertTrue($this->staff->fresh()->can('product-view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('user-view'));
        foreach (['position-view', 'staff-update', 'stock-in-update', 'stock-out-delete', 'report-staff-expense-view'] as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_create_and_update_permissions_are_independent_for_shared_save_routes(): void
    {
        // Validation proves the authorized request reached the real controller;
        // the opposite action must be rejected before business validation runs.
        foreach (['order', 'user', 'product', 'stock-in', 'stock-out', 'stock-transfer'] as $module) {
            $this->staff->syncPermissions([$module . '-create']);
            $this->actingAs($this->staff)->postJson(route('admin-' . $module . '-save'), [])->assertUnprocessable();
            $this->postJson(route('admin-' . $module . '-save', 123), [])->assertForbidden();
            $this->staff->syncPermissions([$module . '-update']);
            $this->postJson(route('admin-' . $module . '-save', 123), [])->assertUnprocessable();
            $this->postJson(route('admin-' . $module . '-save'), [])->assertForbidden();
        }
    }

    public function test_unassigned_module_actions_and_exports_are_denied(): void
    {
        $this->actingAs($this->staff);
        foreach (['product', 'shop', 'customer', 'user', 'category', 'supplier', 'uom', 'staff', 'position', 'staff-expense', 'stock-in', 'stock-out', 'stock-transfer'] as $module) {
            $this->getJson(route('admin-' . $module . '-list', 1))->assertForbidden();
            $this->getJson(route('admin-' . $module . '-create'))->assertForbidden();
            $this->getJson(route('admin-' . $module . '-edit', 1))->assertForbidden();
            foreach (['save', 'delete', 'restore', 'destroy'] as $action) {
                $this->postJson(route('admin-' . $module . '-' . $action, 1), [])->assertForbidden();
            }
        }
        foreach (['dashboard', 'product-export', 'staff-expense-export', 'stock-on-hand-report', 'stock-movement-report',
            'report-sales-report', 'report-order-transaction-report', 'report-inventory-movement-report', 'report-staff-expense-report'] as $route) {
            $this->getJson(route('admin-' . $route))->assertForbidden();
        }
    }

    public function test_sidebar_and_action_controls_follow_assigned_permissions(): void
    {
        $this->staff->givePermissionTo(['user-view', 'category-view']);
        $this->actingAs($this->staff)->get(route('admin-user-list', 1))->assertOk()
            ->assertSee('data-url="' . url('admin/category/list/1') . '"', false)
            ->assertDontSee('data-url="' . url('admin/product/list/1') . '"', false)
            ->assertDontSee('data-url="' . route('admin-dashboard') . '"', false)
            ->assertDontSee('href="' . route('admin-user-permission', $this->manager->id) . '"', false);
        $this->staff->givePermissionTo('user-update');
        $this->get(route('admin-user-list', 1))->assertOk()
            ->assertSee('href="' . route('admin-user-permission', $this->manager->id) . '"', false);
    }

    public function test_lookup_access_respects_the_consuming_form_without_requiring_unrelated_module_access(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        $this->actingAs($this->staff)->getJson(route('admin-select-category'))->assertForbidden();
        $this->staff->givePermissionTo('product-create');
        $this->getJson(route('admin-select-category'))->assertOk();
        $this->getJson(route('admin-category-list', 1))->assertForbidden();
    }

    public function test_export_controls_use_dedicated_permissions_only_where_the_catalog_defines_them(): void
    {
        $this->staff->givePermissionTo(['order-view', 'product-view']);
        $this->actingAs($this->staff);
        $header = function ($module) {
            return view('admin::components.listHeaderComponent', [
                'routeName' => $module, 'showCreate' => false, 'showTabs' => false,
                'filterStatus' => false, 'exportAction' => 'exportRecords()',
            ])->render();
        };
        $this->assertStringContainsString('@click="exportRecords()"', $header('order'));
        $this->assertStringNotContainsString('@click="exportRecords()"', $header('product'));
        $this->staff->givePermissionTo('product-excel');
        $this->assertStringContainsString('@click="exportRecords()"', $header('product'));
    }

    public function test_failed_sync_rolls_back_and_returns_an_error_instead_of_reporting_success(): void
    {
        $this->staff->givePermissionTo('product-view');
        DB::listen(function ($query) {
            if (str_starts_with($query->sql, 'insert into "model_has_permissions"')) {
                throw new \RuntimeException('Simulated assignment failure');
            }
        });
        $this->actingAs($this->manager)->postJson($this->saveUrl(), ['permission' => ['user-view']])
            ->assertStatus(500)->assertJson(['error' => true]);
        $this->assertSame(['product-view'], $this->staff->fresh()->permissions->pluck('name')->all());
    }

    public function test_permission_module_selection_matches_the_configured_menu(): void
    {
        $permissionsBefore = DB::table('permissions')->get()->toJson();
        $modulesBefore = DB::table('module_permissions')->get()->toJson();
        $assignmentsBefore = DB::table('model_has_permissions')->get()->toJson();
        $menuBefore = config('menu');

        // The editor may assign active modules even before the target has access.
        $response = $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))->assertOk();
        $names = $response['groupedModules']->flatten(1)->pluck('name')->sort()->values()->all();
        $expected = ['Dashboard', 'Product', 'Order', 'Shop', 'Customer', 'Stock In', 'Stock Out',
            'Stock Transfer', 'Stock On Hand', 'Stock Movement', 'Report Sales', 'Report Inventory Movement',
            'Staff Expense Report', 'Position', 'Staff', 'Staff Expense', 'User', 'Category', 'Supplier', 'UOM'];
        sort($expected);
        $this->assertSame($expected, $names);
        $response->assertDontSee('value="blog-view"', false)->assertDontSee('value="about-view"', false)
            ->assertDontSee('value="report-transaction-view"', false)->assertSee('value="stock-in-create"', false);
        $this->assertSame($menuBefore, config('menu'));
        $this->assertSame($permissionsBefore, DB::table('permissions')->get()->toJson());
        $this->assertSame($modulesBefore, DB::table('module_permissions')->get()->toJson());
        $this->assertSame($assignmentsBefore, DB::table('model_has_permissions')->get()->toJson());
    }

    public function test_module_selection_follows_menu_additions_removals_and_visibility_flags(): void
    {
        $modules = ModulePermission::with('permission')->get();
        $selection = fn () => PermissionModuleSelection::fromMenu($modules)->pluck('name')->sort()->values()->all();
        $product = ['path' => 'admin/product/list/1', 'permission' => ['product-view'], 'dropDown' => 'disable'];
        config(['menu' => [$product]]);
        $this->assertSame(['Dashboard', 'Product'], $selection()); // dropDown only controls the arrow.
        foreach (['enabled' => false, 'visible' => false, 'hidden' => true, 'disabled' => true] as $flag => $value) {
            config(['menu' => [array_merge($product, [$flag => $value])]]);
            $this->assertSame(['Dashboard'], $selection());
        }
        foreach (['listMenu', 'list-menu', 'children'] as $childrenKey) {
            config(['menu' => [[$childrenKey => [$product], 'permission' => ['about-view']]]]);
            $this->assertSame(['Dashboard', 'Product'], $selection());
            config(['menu' => [[$childrenKey => [$product], 'enabled' => false]]]);
            $this->assertSame(['Dashboard'], $selection());
        }
        config(['menu' => []]);
        $this->assertSame(['Dashboard'], $selection());
        config(['menu' => [['path' => 'admin/blog/list/1', 'permission' => 'blog-view']]]);
        $this->assertSame(['Blog', 'Dashboard'], $selection());
    }

    public function test_module_selection_excludes_container_and_alternative_access_modules(): void
    {
        config(['menu' => [
            ['permission' => ['about-view'], 'children' => [
                ['path' => 'admin/report/sales/daily', 'permission' => ['order-view', 'report-sales-view']],
                ['path' => 'admin/report/inventory-movement/daily', 'permission' => ['stock-movement-view', 'report-inventory-view']],
                ['path' => 'admin/product/list/1', 'permission' => 'product-view', 'hidden' => true],
            ]],
            ['path' => 'admin/file-manager/index'],
        ]]);
        $modules = ModulePermission::with('permission')->get();
        $names = PermissionModuleSelection::fromMenu($modules)->pluck('name')->sort()->values()->all();
        $this->assertSame(['Dashboard', 'Report Inventory Movement', 'Report Sales'], $names);

        // Multiple menu entries may share the same permission module.
        config(['menu' => [
            ['path' => 'admin/order/list/1', 'permission' => 'order-view'],
            ['path' => 'admin/remaining-amount/list/partial', 'permission' => 'order-view'],
        ]]);
        $this->assertSame(['Dashboard', 'Order'], PermissionModuleSelection::fromMenu($modules)->pluck('name')->sort()->values()->all());
    }

    public function test_filtered_form_preserves_existing_assignments_for_excluded_modules(): void
    {
        $this->staff->givePermissionTo(['blog-update', 'product-view']);
        $response = $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))->assertOk()
            ->assertViewHas('preservedPermissions', ['blog-update']);
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//input[@type="checkbox" and @value="blog-update"]')->length);
        $preserved = [];
        foreach ($xpath->query('//form[@id="form"]//input[@type="hidden" and @name="permission[]"]') as $input) {
            $preserved[] = $input->getAttribute('value');
        }
        $this->assertSame(['blog-update'], $preserved);
        $this->postJson($this->saveUrl(), ['permission' => array_merge($preserved, ['product-update'])])->assertOk();
        $this->assertEqualsCanonicalizing(['blog-update', 'product-update'], $this->staff->fresh()->permissions->pluck('name')->all());
        $this->postJson($this->saveUrl(), ['permission' => $preserved])->assertOk();
        $this->assertSame(['blog-update'], $this->staff->fresh()->permissions->pluck('name')->all());
    }
}
