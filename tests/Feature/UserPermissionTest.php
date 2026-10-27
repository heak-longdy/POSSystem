<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ModulePermission;
use App\Http\Controllers\Admin\DashboardController;
use App\Support\PermissionModuleSelection;
use App\Support\AdminPermissions;
use App\Http\Middleware\CheckAdminPermission;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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

    public function test_three_module_user_sees_only_assigned_sidebar_and_search_entries(): void
    {
        $this->staff->syncPermissions(['dashboard-view', 'order-view', 'order-create', 'order-update',
            'order-delete', 'customer-view', 'customer-create', 'customer-update', 'customer-delete']);
        $this->actingAs($this->staff);
        $sidebar = view('admin::shared.sidebar')->render();
        $document = new \DOMDocument();
        @$document->loadHTML($sidebar);
        $links = (new \DOMXPath($document))->query('//a[@data-url]');
        $this->assertSame([route('admin-dashboard'), url('admin/order/list/1'), url('admin/customer/list/1')],
            array_map(fn ($link) => $link->getAttribute('data-url'), iterator_to_array($links)));
        $search = view('admin::components.search_menu')->render();
        foreach (['admin/remaining-amount', 'admin/report/', 'admin/user/list', 'admin/product/list', 'admin/order-bulk-print'] as $path) {
            $this->assertStringNotContainsString($path, $search);
        }

        // Keep the real route/middleware stack, replacing only business queries
        // in these three controllers; the permission fixtures are in SQLite memory.
        foreach ([DashboardController::class, \App\Http\Controllers\Admin\OrderController::class,
            \App\Http\Controllers\Admin\CustomerController::class] as $controller) {
            $this->partialMock($controller, fn ($mock) => $mock->shouldReceive('index')->andReturn(response('Allowed')));
        }
        foreach (['admin-dashboard', 'admin-order-list', 'admin-customer-list'] as $name) {
            $this->get(route($name, $name === 'admin-dashboard' ? [] : ['status' => 1]))->assertOk();
        }
    }

    public function test_three_module_user_cannot_open_or_submit_any_other_module_route(): void
    {
        $this->staff->syncPermissions(['dashboard-view', 'order-view', 'order-create', 'order-update',
            'order-delete', 'customer-view', 'customer-create', 'customer-update', 'customer-delete']);
        $this->actingAs($this->staff);
        $checked = 0;
        foreach (Route::getRoutes() as $route) {
            if (!in_array(CheckAdminPermission::class, $route->middleware(), true)) continue;
            $name = $route->getName();
            if (in_array($name, ['admin-home', 'admin-dashboard', 'admin-report-customer-report'], true)
                || str_starts_with($name, 'admin-select-')
                || preg_match('/^admin-(order-(?!bulk-print-)|customer-(?!paid-))/', $name)) continue;
            $parameters = array_fill_keys($route->parameterNames(), 1);
            if (isset($parameters['type'])) $parameters['type'] = 'privacy-policy';
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $response = $this->json($method, route($name, $parameters));
                $this->assertSame(403, $response->status(), $method . ' ' . $name);
                $checked++;
            }
        }
        $this->assertGreaterThan(300, $checked);
        $this->get(route('admin-report-sales-daily'))->assertForbidden();
        $this->get(route('admin-remaining-amount-list', 'partial'), ['X-MDI-Partial' => '1'])->assertForbidden();
    }

    public function test_all_registered_protected_routes_have_an_explicit_permission_mapping(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (!in_array(CheckAdminPermission::class, $route->middleware(), true) || $route->getName() === 'admin-home') continue;
            $parameters = array_fill_keys($route->parameterNames(), 1);
            if (isset($parameters['type'])) $parameters['type'] = 'privacy-policy';
            $request = Request::create(route($route->getName(), $parameters), $route->methods()[0]);
            $boundRoute = (clone $route)->bind($request);
            $request->setRouteResolver(fn () => $boundRoute);
            $this->assertNotEmpty(AdminPermissions::forRequest($request), $route->getName());
        }
        Route::middleware(['AdminGuard', CheckAdminPermission::class])
            ->get('/admin/unmapped-module', fn () => 'must not run')->name('admin-unmapped-module');
        $this->actingAs($this->staff)->getJson('/admin/unmapped-module')->assertForbidden();
    }

    public function test_report_permissions_do_not_grant_other_report_modules(): void
    {
        foreach ([
            'report-sales-view' => ['admin-report-sales-daily', 'admin/report/sales/daily'],
            'report-inventory-view' => ['admin-report-inventory-movement-daily', 'admin/report/inventory-movement/daily'],
            'report-staff-expense-view' => ['admin-report-staff-expense-daily', 'admin/report/staff-expense/daily'],
            'report-transaction-view' => ['admin-report-order-transaction-daily', null],
        ] as $permission => [$allowedRoute, $path]) {
            $this->staff->syncPermissions([$permission]);
            $this->actingAs($this->staff);
            $request = Request::create(route($allowedRoute));
            $request->setRouteResolver(fn () => Route::getRoutes()->match($request));
            $request->setUserResolver(fn () => $this->staff);
            $this->assertSame('allowed', (new CheckAdminPermission)->handle($request, fn () => 'allowed'));
            if ($path) $this->assertStringContainsString('data-url="' . url($path) . '"', view('admin::shared.sidebar')->render());
            foreach (['admin-report-sales-daily', 'admin-report-inventory-movement-daily',
                'admin-report-staff-expense-daily', 'admin-report-order-transaction-daily'] as $otherRoute) {
                if ($otherRoute !== $allowedRoute) $this->getJson(route($otherRoute))->assertForbidden();
            }
        }
    }

    public function test_remaining_amount_requires_its_own_permission_and_super_admin_keeps_access(): void
    {
        $this->partialMock(\App\Http\Controllers\Admin\RemainingAmountController::class,
            fn ($mock) => $mock->shouldReceive('index')->andReturn(response('Allowed')));
        // No live catalog changes: the dedicated grant is deliberately absent.
        $this->staff->syncPermissions(['order-view', 'order-update', 'order-delete']);
        $this->actingAs($this->staff)->getJson(route('admin-remaining-amount-list', 'partial'))->assertForbidden();
        $this->postJson(route('admin-remaining-amount-add-payment', 1))->assertForbidden();
        $this->deleteJson(route('admin-remaining-amount-delete-payment', 1))->assertForbidden();

        // Model a future explicit grant using disposable test fixtures only.
        Permission::create(['name' => 'remaining-amount-view', 'guard_name' => 'web']);
        $this->staff->syncPermissions(['remaining-amount-view']);
        $this->get(route('admin-remaining-amount-list', 'partial'))->assertOk();
        $this->postJson(route('admin-remaining-amount-add-payment', 1))->assertForbidden();
        $this->getJson(route('admin-order-list', 1))->assertForbidden();
        $this->actingAs(new User(['role' => 'super_admin']))
            ->get(route('admin-remaining-amount-list', 'partial'))->assertOk();
    }

    public function test_workspace_scope_changes_when_user_or_effective_permissions_change(): void
    {
        $this->actingAs($this->staff);
        $initial = AdminPermissions::workspaceScope();
        $this->assertSame($initial, AdminPermissions::workspaceScope());
        $this->staff->givePermissionTo('order-view');
        $this->assertNotSame($initial, $granted = AdminPermissions::workspaceScope());
        $this->staff->syncPermissions([]);
        $this->assertNotSame($granted, AdminPermissions::workspaceScope());
        $this->actingAs($this->manager);
        $this->assertNotSame($initial, AdminPermissions::workspaceScope());
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
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
        $this->manager->givePermissionTo('user-create');
        $this->actingAs($this->manager)->post(route('admin-user-save'), [
            'name' => 'New cashier', 'email' => 'cashier@example.test', 'status' => 1,
            'language_preference' => 'en',
            'password' => 'test-password', 'confirm_password' => 'test-password',
        ])->assertRedirect(route('admin-user-list', 1));
        $user = User::where('email', 'cashier@example.test')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertSame(['dashboard-view'], $user->getAllPermissions()->pluck('name')->all());
        $this->assertCount(0, $user->roles);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('test-password', $user->password));
        $this->get(route('admin-user-permission', $user->id))->assertOk()
            ->assertViewHas('userPermissions', ['dashboard-view'])
            ->assertViewHas('inheritedPermissions', []);
        $this->postJson($this->saveUrl($user), ['permission' => ['dashboard-view', 'user-view']])->assertOk();
        $this->post(route('admin-user-save', $user->id), [
            'name' => 'Updated cashier', 'email' => $user->email, 'status' => 1,
            'language_preference' => 'en',
        ])->assertRedirect(route('admin-user-list', 1));
        $this->assertSame('Updated cashier', $user->fresh()->name);
        $this->assertEqualsCanonicalizing(['dashboard-view', 'user-view'], $user->fresh()->getAllPermissions()->pluck('name')->all());
        $this->actingAs($user->fresh())->get(route('admin-user-list', 1))->assertOk();
        $this->getJson(route('admin-user-create'))->assertForbidden();
        $this->actingAs($this->manager)->postJson($this->saveUrl($user), ['permission' => ['dashboard-view']])->assertOk();
        $this->actingAs($user->fresh())->getJson(route('admin-user-list', 1))->assertForbidden();
        $this->assertSame('admin', $user->fresh()->role);
        $this->assertCount(Permission::count(), $adminRole->fresh()->permissions);
    }

    public function test_creation_and_edit_forms_omit_role(): void
    {
        $this->manager->givePermissionTo('user-create');
        $this->actingAs($this->manager)->get(route('admin-user-create'))->assertOk()->assertDontSee('name="role"', false);
        $this->get(route('admin-user-edit', $this->staff->id))->assertOk()->assertDontSee('name="role"', false);
    }

    public function test_edit_preserves_existing_role_and_permissions_even_if_a_role_is_submitted(): void
    {
        $role = Role::create(['name' => 'cashier', 'guard_name' => 'web']);
        $role->givePermissionTo('user-view');
        $this->staff->update(['role' => 'cashier']);
        $this->staff->assignRole($role);
        $this->staff->givePermissionTo('product-view');
        Role::create(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());

        $this->actingAs($this->manager)->post(route('admin-user-save', $this->staff->id), [
            'name' => 'Updated staff', 'email' => $this->staff->email, 'status' => 1,
            'language_preference' => 'en', 'role' => 'admin',
        ])->assertRedirect(route('admin-user-list', 1))->assertSessionHas('success');

        $staff = $this->staff->fresh();
        $this->assertSame('Updated staff', $staff->name);
        $this->assertSame('cashier', $staff->role);
        $this->assertSame(['cashier'], $staff->getRoleNames()->all());
        $this->assertSame(['product-view'], $staff->permissions->pluck('name')->all());
        $this->assertSame(['user-view'], $staff->getPermissionsViaRoles()->pluck('name')->all());
        $this->assertFalse($staff->can('product-update'));
    }

    public function test_default_admin_has_only_dashboard_access_even_when_admin_role_has_all_permissions(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::all());
        $this->manager->givePermissionTo('user-create');
        $this->actingAs($this->manager)->post(route('admin-user-save'), [
            'name' => 'Dashboard user', 'email' => 'dashboard@example.test', 'status' => 1,
            'language_preference' => 'en', 'password' => 'test-password', 'confirm_password' => 'test-password',
        ])->assertSessionHas('success');
        $user = User::where('email', 'dashboard@example.test')->firstOrFail();
        foreach (Permission::pluck('name') as $permission) {
            $this->assertSame($permission === 'dashboard-view', $user->can($permission), $permission);
        }

        // Exercise real route authorization and sidebar rendering without the
        // unrelated Dashboard analytics tables / MySQL aggregate functions.
        $this->partialMock(DashboardController::class, function ($mock) {
            $mock->shouldReceive('index')->once()->andReturnUsing(fn () => response()->view('admin::shared.sidebar'));
        });
        $response = $this->actingAs($user)->get(route('admin-dashboard'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $links = (new \DOMXPath($document))->query('//ul[contains(@class,"side-menu")]//a[@data-url]');
        $this->assertSame([route('admin-dashboard')], array_map(fn ($link) => $link->getAttribute('data-url'), iterator_to_array($links)));

        $checkMenu = function ($items) use (&$checkMenu) {
            foreach ($items as $item) {
                if (!empty($item['path'])) {
                    $this->getJson(url($item['path']))->assertForbidden();
                }
                $checkMenu($item['listMenu'] ?? $item['children'] ?? []);
            }
        };
        $checkMenu(config('menu'));
        foreach (['user-save-permission', 'user-save', 'order-save', 'product-save'] as $route) {
            $this->postJson(route('admin-' . $route, $this->manager->id), [])->assertForbidden();
        }
        foreach (['admin-setting-invoice-save', 'admin-setting-invoice-preview',
            'admin-file-manager-upload', 'admin-file-manager-rename-file', 'admin-file-manager-create-folder',
            'admin-file-manager-rename-folder', 'admin-our-service-store', 'admin-setting-save'] as $route) {
            $this->postJson(route($route), [])->assertForbidden();
        }
        foreach (['first', 'files', 'folders'] as $action) {
            $this->getJson(route('admin-file-manager-' . $action))->assertForbidden();
        }
        foreach (['delete-file', 'delete-folder', 'delete-all'] as $action) {
            $this->deleteJson(route('admin-file-manager-' . $action))->assertForbidden();
        }
        $this->putJson(route('admin-file-manager-restore-all'))->assertForbidden();
        $this->getJson(url('admin/setting/invoice'))->assertForbidden();
    }

    public function test_file_and_invoice_access_can_be_granted_and_revoked_through_permission_configuration(): void
    {
        $this->staff->update(['role' => 'admin']);
        $this->staff->givePermissionTo('dashboard-view');
        $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))->assertOk()
            ->assertSee('value="file-manager-view"', false)->assertSee('value="setting-invoice-update"', false);
        $this->postJson($this->saveUrl(), ['permission' => ['dashboard-view', 'file-manager-view', 'setting-invoice-update']])->assertOk();
        $this->actingAs($this->staff->fresh())->get(route('admin-file-manager-index'))->assertOk();
        $this->postJson(route('admin-file-manager-upload'), [])->assertForbidden();
        $this->postJson(route('admin-setting-invoice-save'), [])->assertUnprocessable();
        $this->getJson(route('admin-setting-invoice-index'))->assertForbidden();
        $this->actingAs($this->manager)->postJson($this->saveUrl(), ['permission' => ['dashboard-view']])->assertOk();
        $this->actingAs($this->staff->fresh())->getJson(route('admin-file-manager-index'))->assertForbidden();
        $this->postJson(route('admin-setting-invoice-save'), [])->assertForbidden();
    }

    public function test_order_bulk_print_access_can_be_granted_and_revoked_through_permission_configuration(): void
    {
        $this->staff->update(['role' => 'admin']);
        $this->staff->givePermissionTo('dashboard-view');
        $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))->assertOk()
            ->assertSee('value="order-bulk-print-view"', false);
        $this->postJson($this->saveUrl(), ['permission' => ['dashboard-view', 'order-bulk-print-view']])->assertOk();
        $this->assertTrue($this->staff->fresh()->can('order-bulk-print-view'));
        $this->actingAs($this->manager)->postJson($this->saveUrl(), ['permission' => ['dashboard-view']])->assertOk();
        $this->assertFalse($this->staff->fresh()->can('order-bulk-print-view'));
    }

    public function test_create_ignores_submitted_roles_and_permissions_including_for_super_admin_creators(): void
    {
        $super = User::create(['name' => 'Super', 'email' => 'super@example.test', 'role' => 'super_admin']);
        $this->actingAs($super);
        foreach (['admin', 'cashier', 'super_admin'] as $submittedRole) {
            $this->from(route('admin-user-create'))->post(route('admin-user-save'), [
                'name' => 'New user', 'email' => $submittedRole . '@example.test', 'status' => 1,
                'language_preference' => 'en', 'password' => 'test-password', 'confirm_password' => 'test-password',
                'role' => $submittedRole, 'roles' => ['super_admin'], 'permission' => ['user-update'],
                'permissions' => ['product-view'], 'save_opt' => 'save_new',
            ])->assertRedirect(route('admin-user-create'))->assertSessionHas('success');
            $user = User::where('email', $submittedRole . '@example.test')->firstOrFail();
            $this->assertSame('admin', $user->role);
            $this->assertCount(0, $user->roles);
            $this->assertSame(['dashboard-view'], $user->getAllPermissions()->pluck('name')->all());
        }
        $this->postJson(route('admin-user-save'), [
            'id' => $super->id, 'name' => 'No password', 'email' => 'invalid@example.test',
            'status' => 1, 'language_preference' => 'en',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password', 'confirm_password']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.test']);
    }

    public function test_failed_default_permission_assignment_rolls_back_user_creation(): void
    {
        Permission::where('name', 'dashboard-view')->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->manager->givePermissionTo('user-create');
        $this->actingAs($this->manager)->from(route('admin-user-create'))->post(route('admin-user-save'), [
            'name' => 'Failed user', 'email' => 'failed@example.test', 'status' => 1,
            'language_preference' => 'en', 'password' => 'test-password', 'confirm_password' => 'test-password',
        ])->assertRedirect(route('admin-user-create'))->assertSessionHas('warning')->assertSessionMissing('success');
        $this->assertDatabaseMissing('users', ['email' => 'failed@example.test']);
        $this->assertArrayNotHasKey('password', session()->getOldInput());
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
        $migration = require database_path('migrations/2026_10_16_000000_add_file_and_invoice_permissions.php');
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
        $menu = $response['permissionMenu'];
        $names = PermissionModuleSelection::flatten($menu)->pluck('name')->all();
        $expected = ['Dashboard', 'Product', 'Order', 'Order Bulk Print', 'Shop', 'Customer', 'Stock In', 'Stock Out',
            'Stock Transfer', 'Stock On Hand', 'Stock Movement', 'Report Sales', 'Consolidated Product Quantity', 'Report Inventory Movement',
            'Staff Expense Report', 'Position', 'Staff', 'Staff Expense', 'User', 'File Manager', 'Category', 'Supplier', 'UOM', 'Invoice Setting'];
        $this->assertSame($expected, $names);
        $groups = $menu->filter(fn ($item) => isset($item['children']))->values();
        $this->assertSame(['Inventory Management', 'Report Management', 'Setting & Application'],
            $groups->map(fn ($item) => $item['label']['en'])->all());
        $this->assertSame(['Stock In', 'Stock Out', 'Stock Transfer', 'Stock On Hand', 'Stock Movement'],
            PermissionModuleSelection::flatten($groups[0]['children'])->pluck('name')->all());
        $this->assertSame(['Report Sales', 'Consolidated Product Quantity', 'Report Inventory Movement', 'Staff Expense Report'],
            PermissionModuleSelection::flatten($groups[1]['children'])->pluck('name')->all());
        $settings = $groups[2]['children'];
        $this->assertSame(['Position', 'Staff', 'Staff Expense', 'User'],
            $settings->take(4)->map(fn ($item) => $item['module']->name)->all());
        $this->assertSame('File Manager', $settings[4]['module']->name);
        $this->assertSame('Setting', $settings[5]['label']['en']);
        $this->assertSame(['Category', 'Supplier', 'UOM', 'Invoice Setting'],
            PermissionModuleSelection::flatten($settings[5]['children'])->pluck('name')->all());

        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $cards = $xpath->query('//div[contains(concat(" ", @class, " "), " permissionItem ")]');
        $this->assertSame(PermissionModuleSelection::flatten($menu)->pluck('id')->map(fn ($id) => (string) $id)->all(),
            array_map(fn ($card) => $card->getAttribute('data-module-id'), iterator_to_array($cards)));
        $this->assertSame(1, $xpath->query('//div[@data-group="Setting & Application"]//div[@data-group="Setting"]')->length);
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

    public function test_permission_menu_follows_nested_reordering_and_keeps_shared_modules_once(): void
    {
        // Database order and parent labels must not determine the menu hierarchy.
        ModulePermission::query()->update(['sort_no' => 0, 'parent_name' => 'Old group']);
        $modules = ModulePermission::with('permission')->orderByDesc('id')->get();
        $modulesBefore = $modules->toJson();
        $product = ['path' => 'admin/product/list/1', 'permission' => 'product-view'];
        $supplier = ['path' => 'admin/supplier/list/1', 'permission' => 'supplier-view'];
        $user = ['path' => 'admin/user/list/1', 'permission' => 'user-view'];
        config(['menu' => [
            ['label' => 'First', 'list-menu' => [
                ['name' => 'Nested', 'children' => [$supplier, $product]],
                $user,
            ]],
            ['path' => 'admin/shop/list/1', 'permission' => 'shop-view'],
            // A different permission belonging to Product still uses one card.
            ['label' => 'Duplicate only', 'children' => [array_merge($product, ['permission' => 'product-update'])]],
            ['label' => 'Hidden', 'hidden' => true, 'children' => [
                ['path' => 'admin/category/list/1', 'permission' => 'category-view'],
            ]],
        ]]);
        $menu = PermissionModuleSelection::treeFromMenu($modules);
        $this->assertSame(['Dashboard', 'Supplier', 'Product', 'User', 'Shop'],
            PermissionModuleSelection::flatten($menu)->pluck('name')->all());
        $this->assertCount(3, $menu); // Dashboard, First, Shop; no empty groups.
        $this->assertSame('First', $menu[1]['label']);
        $this->assertSame('Nested', $menu[1]['children'][0]['label']);
        $this->assertSame(['Supplier', 'Product'],
            PermissionModuleSelection::flatten($menu[1]['children'][0]['children'])->pluck('name')->all());
        $this->assertSame($modulesBefore, $modules->toJson());

        config(['menu' => [$user, ['label' => 'First', 'listMenu' => [$product, $supplier]]]]);
        $this->assertSame(['Dashboard', 'User', 'Product', 'Supplier'],
            PermissionModuleSelection::fromMenu($modules)->pluck('name')->all());
    }

    public function test_permission_menu_uses_khmer_menu_labels_and_searches_ancestor_groups(): void
    {
        $this->manager->update(['language_preference' => 'km']);
        $response = $this->actingAs($this->manager)->get(route('admin-user-permission', $this->staff->id))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="utf-8" ?>' . $response->getContent());
        $xpath = new \DOMXPath($document);
        $settings = $xpath->query('//div[@data-group="ការកំណត់ និងកម្មវិធី"]//div[@data-group="ការកំណត់"]');
        $this->assertSame(1, $settings->length);
        $cards = $xpath->query('.//div[contains(concat(" ", @class, " "), " permissionItem ")]', $settings->item(0));
        $this->assertSame(4, $cards->length);
        foreach ($cards as $card) {
            $this->assertStringContainsString('ការកំណត់ និងកម្មវិធី', $card->getAttribute('data-group'));
            $this->assertStringContainsString('Setting & Application', $card->getAttribute('data-group'));
        }
        $positionId = ModulePermission::where('name', 'Position')->value('id');
        $positionLabel = $xpath->query('//div[@data-module-id="' . $positionId . '"]//div[contains(@class,"permissionHeader")]/div[1]/span')->item(0);
        $this->assertSame('មុខតំណែង', trim($positionLabel->textContent));
        $checkboxes = $xpath->query('//input[@name="permission[]" and @type="checkbox"]');
        $values = array_map(fn ($input) => $input->getAttribute('value'), iterator_to_array($checkboxes));
        $this->assertCount(count(array_unique($values)), $values);
        $this->assertContains('product-update', $values);
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
