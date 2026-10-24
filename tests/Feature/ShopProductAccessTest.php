<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopProductAccessTest extends TestCase
{
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('language_preference')->default('en');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->date('order_date');
            $table->double('total_price');
            $table->double('total_commission');
            $table->softDeletes();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('price');
            $table->integer('status')->default(1);
            $table->softDeletes();
        });
        foreach (['categories', 'uoms'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->softDeletes();
            });
        }
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('product_id');
            $table->double('price');
            $table->double('point')->nullable();
            $table->integer('max_qty')->nullable();
            $table->double('commission')->nullable();
            $table->string('commission_type')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('shops')->insert(['id' => 1, 'name' => 'Central Shop']);
        DB::table('products')->insert(['id' => 1, 'name' => 'Coffee', 'price' => 3]);
        (new PermissionSeeder())->run();
        $this->staff = User::create([
            'name' => 'Shop staff', 'email' => 'shop@example.test',
            'role' => 'staff', 'language_preference' => 'en',
        ]);
    }

    public function test_super_admin_sees_products_without_a_direct_add_product_action(): void
    {
        $this->actingAs(new User(['role' => 'super_admin', 'language_preference' => 'en']));
        $this->get(route('admin-shop-list', 1))->assertOk()
            ->assertSee('href="' . route('admin-shop-product', 1) . '"', false)
            ->assertDontSee('href="' . route('admin-shop-product-create', 1) . '"', false)
            ->assertDontSee('Add Product to Shop');
    }

    public function test_shop_viewer_cannot_open_the_product_section(): void
    {
        $this->staff->givePermissionTo('shop-view');
        $this->actingAs($this->staff);
        $this->get(route('admin-shop-list', 1))->assertOk()
            ->assertDontSee('href="' . route('admin-shop-product', 1) . '"', false)
            ->assertDontSee('href="' . route('admin-shop-product-create', 1) . '"', false);
        $this->get(route('admin-shop-product', 1))->assertForbidden();
        $this->get(route('admin-shop-product-create', 1))->assertForbidden();
        $this->post(route('admin-shop-product-save', 1), [])->assertForbidden();
    }

    public function test_product_permission_allows_viewing_and_managing_shop_products(): void
    {
        $this->staff->givePermissionTo(['shop-view', 'shop-product']);
        $this->actingAs($this->staff);
        $this->get(route('admin-shop-list', 1))->assertOk()
            ->assertSee('href="' . route('admin-shop-product', 1) . '"', false)
            ->assertDontSee('href="' . route('admin-shop-product-create', 1) . '"', false);
        $this->get(route('admin-shop-product', 1))->assertOk()
            ->assertSee('s-click-link="' . route('admin-shop-product-create', 1) . '"', false);
        $this->get(route('admin-shop-product-create', 1))->assertOk()
            ->assertSee('Central Shop')->assertSee('Coffee');
        $this->post(route('admin-shop-product-save', 1), [
            'products' => [['product_id' => 1, 'price' => 2.5, 'status' => 1]],
        ])->assertRedirect(route('admin-shop-product', 1));
        $this->assertDatabaseHas('shop_products', [
            'shop_id' => 1, 'product_id' => 1, 'price' => 2.5, 'status' => 1,
        ]);
        $this->get(route('admin-shop-product', 1))->assertOk()->assertSee('Coffee');

        $shopProductId = DB::table('shop_products')->value('id');
        $parameters = ['shopId' => 1, 'shopProductId' => $shopProductId];
        $this->get(route('admin-shop-product-edit', $parameters))->assertOk();
        $this->post(route('admin-shop-product-save', 1), [
            'products' => [['id' => $shopProductId, 'product_id' => 1, 'price' => 4, 'status' => 1]],
        ])->assertRedirect(route('admin-shop-product', 1));
        $this->assertDatabaseHas('shop_products', ['id' => $shopProductId, 'price' => 4]);
        $this->postJson(route('admin-shop-product-delete', $parameters))->assertOk()->assertJson(['status' => 200]);
        $this->assertSoftDeleted('shop_products', ['id' => $shopProductId]);
        $this->get(route('admin-shop-product', ['id' => 1, 'status' => 'trash']))->assertOk()->assertSee('Coffee');
        $this->postJson(route('admin-shop-product-restore', $parameters))->assertOk()->assertJson(['status' => 200]);
        $this->assertDatabaseHas('shop_products', ['id' => $shopProductId, 'deleted_at' => null]);
        $this->postJson(route('admin-shop-product-delete', $parameters))->assertOk();
        $this->postJson(route('admin-shop-product-destroy', $parameters))->assertOk()->assertJson(['status' => 200]);
        $this->assertDatabaseMissing('shop_products', ['id' => $shopProductId]);

        // Product management does not grant editing of the shop itself.
        $this->get(route('admin-shop-edit', 1))->assertForbidden();
    }

    public function test_other_shop_and_catalog_permissions_do_not_allow_product_management(): void
    {
        $this->staff->givePermissionTo([
            'shop-view', 'shop-create', 'shop-update', 'shop-delete',
            'product-view', 'product-create', 'product-update', 'product-delete',
        ]);
        $this->actingAs($this->staff);
        $this->get(route('admin-shop-list', 1))->assertOk()
            ->assertDontSee('href="' . route('admin-shop-product', 1) . '"', false)
            ->assertDontSee('href="' . route('admin-shop-product-create', 1) . '"', false);
        $this->get(route('admin-shop-product', 1))->assertForbidden();
        $this->get(route('admin-shop-product-create', 1))->assertForbidden();
        $this->post(route('admin-shop-product-save', 1), [])->assertForbidden();
        $parameters = ['shopId' => 1, 'shopProductId' => 1];
        $this->get(route('admin-shop-product-edit', $parameters))->assertForbidden();
        foreach (['delete', 'restore', 'destroy'] as $action) {
            $this->postJson(route('admin-shop-product-' . $action, $parameters))->assertForbidden();
        }
    }

    public function test_product_checkbox_is_under_shop_and_can_be_saved_and_revoked(): void
    {
        $admin = new User(['role' => 'super_admin', 'language_preference' => 'en']);
        $this->actingAs($admin);
        $response = $this->get(route('admin-user-permission', $this->staff->id))->assertOk();
        $moduleId = DB::table('module_permissions')->where('name', 'Shop')->value('id');
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $inputs = (new \DOMXPath($document))->query(
            '//div[@data-module-id="' . $moduleId . '"]//input[@name="permission[]" and @value="shop-product"]'
        );
        $this->assertSame(1, $inputs->length);
        $this->assertSame('Product', __('permission.permission.shop-product', [], 'en'));
        $this->assertSame('ផលិតផល', __('permission.permission.shop-product', [], 'km'));

        $url = route('admin-user-save-permission', $this->staff->id);
        $this->postJson($url, ['permission' => ['shop-view', 'shop-product']])->assertOk()->assertJson(['error' => false]);
        $this->assertTrue($this->staff->fresh()->can('shop-product'));
        $this->actingAs($this->staff->fresh())->get(route('admin-shop-product', 1))->assertOk();

        $this->actingAs($admin)->postJson($url, ['permission' => ['shop-view']])->assertOk();
        $this->assertFalse($this->staff->fresh()->can('shop-product'));
        $this->actingAs($this->staff->fresh())->get(route('admin-shop-product', 1))->assertForbidden();
    }

    public function test_migration_adds_only_the_product_permission_and_preserves_assignments(): void
    {
        Permission::findByName('shop-product')->delete();
        $this->staff->givePermissionTo('shop-update');
        $role = Role::create(['name' => 'shop-editor', 'guard_name' => 'web']);
        $role->givePermissionTo('shop-update');
        $permissions = DB::table('permissions')->pluck('id', 'name')->all();
        $modules = DB::table('module_permissions')->pluck('id', 'name')->all();
        $assignments = DB::table('model_has_permissions')->get()->toJson();
        $roleAssignments = DB::table('role_has_permissions')->get()->toJson();

        $migration = require database_path('migrations/2026_10_24_000000_add_shop_product_permission.php');
        $migration->up();
        $permission = Permission::findByName('shop-product');
        $this->assertSame($modules['Shop'], $permission->module_id);
        $this->assertSame('Product', $permission->display_name);
        $migration->up();
        $this->assertSame($permission->id, Permission::findByName('shop-product')->id);
        $this->assertSame($permissions, DB::table('permissions')->where('name', '!=', 'shop-product')->pluck('id', 'name')->all());
        $this->assertSame($modules, DB::table('module_permissions')->pluck('id', 'name')->all());
        $this->assertSame($assignments, DB::table('model_has_permissions')->get()->toJson());
        $this->assertSame($roleAssignments, DB::table('role_has_permissions')->get()->toJson());
        $this->assertFalse($this->staff->fresh()->can('shop-product'));

        $this->staff->givePermissionTo('shop-product');
        $migration->up();
        $this->assertTrue($this->staff->fresh()->can('shop-product'));
    }

    public function test_trashed_shops_do_not_offer_product_actions(): void
    {
        Shop::findOrFail(1)->delete();
        $this->actingAs(new User(['role' => 'super_admin', 'language_preference' => 'en']));
        $this->get(route('admin-shop-list', 'trash'))->assertOk()
            ->assertDontSee('href="' . route('admin-shop-product', 1) . '"', false)
            ->assertDontSee('href="' . route('admin-shop-product-create', 1) . '"', false);
    }
}
