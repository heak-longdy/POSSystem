<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopDeleteRestrictionTest extends TestCase
{
    private User $admin;

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
            $table->integer('type_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('barber_id')->nullable();
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('image')->nullable();
            $table->string('password')->nullable();
            $table->double('total_wallet')->default(0);
            $table->integer('total_point')->default(0);
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
            $table->timestamps();
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

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'role' => 'super_admin',
            'language_preference' => 'en',
        ]);

        Product::create(['id' => 1, 'name' => 'Latte', 'price' => 3.5]);
        Product::create(['id' => 2, 'name' => 'Croissant', 'price' => 2.5]);
    }

    public function test_shop_cannot_be_deleted_if_it_has_configured_product_items(): void
    {
        $shop = Shop::create(['id' => 10, 'name' => 'Coffee Branch 1', 'status' => 1]);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 1,
            'price' => 3.5,
            'status' => 1,
        ]);

        $this->assertTrue(ShopProduct::where('shop_id', $shop->id)->exists());

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-delete', $shop->id));

        $response->assertStatus(200)
            ->assertSessionHas('warning', __('shop.message.cannot_delete_has_products'))
            ->assertJson([
                'message' => 'unsuccess',
                'status' => 200,
                'error' => __('shop.message.cannot_delete_has_products'),
            ]);

        $this->assertNull($shop->fresh()->deleted_at);
        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'deleted_at' => null]);
    }

    public function test_shop_can_only_be_deleted_if_no_product_items_are_configured_in_that_shop(): void
    {
        $shop = Shop::create(['id' => 20, 'name' => 'Empty Branch', 'status' => 1]);

        $this->assertFalse(ShopProduct::where('shop_id', $shop->id)->exists());

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-delete', $shop->id));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'status' => 200,
            ]);

        $this->assertNotNull($shop->fresh()->deleted_at);
    }

    public function test_shop_can_be_deleted_after_all_its_product_items_are_deleted(): void
    {
        $shop = Shop::create(['id' => 30, 'name' => 'Transitional Branch', 'status' => 1]);

        $shopProduct = ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 1,
            'price' => 3.5,
            'status' => 1,
        ]);

        $this->assertTrue(ShopProduct::where('shop_id', $shop->id)->exists());

        // Attempting delete should fail
        $this->actingAs($this->admin);
        $this->post(route('admin-shop-delete', $shop->id))
            ->assertStatus(200)
            ->assertSessionHas('warning', __('shop.message.cannot_delete_has_products'))
            ->assertJson([
                'message' => 'unsuccess',
                'status' => 200,
                'error' => __('shop.message.cannot_delete_has_products'),
            ]);

        // Delete the product item from the shop (soft delete, as done by deleteProduct)
        $shopProduct->delete();

        // Now shop has no active product items configured
        $this->assertFalse(ShopProduct::where('shop_id', $shop->id)->exists());

        // Now deleting the shop should succeed
        $response = $this->post(route('admin-shop-delete', $shop->id));
        $response->assertStatus(200)
            ->assertJson(['message' => 'success', 'status' => 200]);

        $this->assertNotNull($shop->fresh()->deleted_at);
    }

    public function test_shop_cannot_be_destroyed_if_it_has_configured_product_items(): void
    {
        $shop = Shop::create(['id' => 40, 'name' => 'Permanent Test Branch', 'status' => 1]);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 2,
            'price' => 2.5,
            'status' => 1,
        ]);

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-destroy', $shop->id));

        $response->assertStatus(200)
            ->assertSessionHas('warning', __('shop.message.cannot_delete_has_products'))
            ->assertJson([
                'message' => 'unsuccess',
                'status' => 200,
                'error' => __('shop.message.cannot_delete_has_products'),
            ]);

        $this->assertDatabaseHas('shops', ['id' => $shop->id]);
    }

    public function test_shop_can_be_destroyed_if_no_product_items_are_configured(): void
    {
        $shop = Shop::create(['id' => 50, 'name' => 'To Be Destroyed', 'status' => 1]);
        $shop->delete(); // Move to trash first

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-destroy', $shop->id));

        $response->assertStatus(200)
            ->assertJson(['message' => 'success', 'status' => 200]);

        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_can_create_new_shop_record(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-save'), [
            'name' => 'Newly Created Shop',
            'phone' => '0987654321',
            'address' => 'Street 123, Phnom Penh',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('shops', [
            'name' => 'Newly Created Shop',
            'phone' => '0987654321',
        ]);
    }

    public function test_disabled_product_items_still_count_as_configured(): void
    {
        $shop = Shop::create(['id' => 80, 'name' => 'Disabled Item Branch', 'status' => 1]);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 1,
            'price' => 3.5,
            'status' => 2, // disabled/inactive
        ]);

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-delete', $shop->id));

        $response->assertStatus(200)
            ->assertSessionHas('warning', __('shop.message.cannot_delete_has_products'))
            ->assertJson([
                'message' => 'unsuccess',
                'status' => 200,
                'error' => __('shop.message.cannot_delete_has_products'),
            ]);

        $this->assertNull($shop->fresh()->deleted_at);
    }

    public function test_destroying_shop_cleans_up_orphaned_trashed_shop_products(): void
    {
        $shop = Shop::create(['id' => 90, 'name' => 'Cleanup Branch', 'status' => 1]);

        $shopProduct = ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 1,
            'price' => 3.5,
            'status' => 1,
        ]);

        $shopProduct->delete(); // trashed in shop
        $shop->delete(); // trashed shop

        $this->actingAs($this->admin);
        $response = $this->post(route('admin-shop-destroy', $shop->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
        $this->assertDatabaseMissing('shop_products', ['id' => $shopProduct->id]);
    }

    public function test_khmer_translation_returned_when_in_khmer(): void
    {
        $khmerAdmin = User::create([
            'name' => 'Khmer Admin',
            'email' => 'khmer_admin@example.test',
            'role' => 'super_admin',
            'language_preference' => 'km',
        ]);

        $shop = Shop::create(['id' => 100, 'name' => 'Khmer Branch', 'status' => 1]);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => 1,
            'price' => 3.5,
            'status' => 1,
        ]);

        $this->actingAs($khmerAdmin);
        $response = $this->post(route('admin-shop-delete', $shop->id));

        $response->assertStatus(200)
            ->assertSessionHas('warning', 'មិនអាចលុបហាងនេះបានទេ ពីព្រោះមានទំនិញត្រូវបានកំណត់នៅក្នុងហាងនេះ។')
            ->assertJson([
                'message' => 'unsuccess',
                'status' => 200,
                'error' => 'មិនអាចលុបហាងនេះបានទេ ពីព្រោះមានទំនិញត្រូវបានកំណត់នៅក្នុងហាងនេះ។',
            ]);
    }
}
