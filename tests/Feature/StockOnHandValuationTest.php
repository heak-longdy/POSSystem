<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\StockOnHand;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockOnHandValuationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('permissions')) {
            require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
            (new \CreatePermissionTables())->up();
        }

        // Isolated test tables
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('stock_on_hands');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('uoms');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('users');
        Schema::dropIfExists('barbers');

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('uoms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->double('cost')->nullable();
            $table->double('price')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('uom_id')->nullable();
            $table->integer('status')->default(1);
            $table->double('commission')->nullable();
            $table->string('image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('product_id');
            $table->double('price')->nullable();
            $table->double('point')->nullable();
            $table->integer('max_qty')->default(0);
            $table->double('commission')->nullable();
            $table->string('commission_type')->nullable();
            $table->integer('status')->default(1);
            $table->integer('promotion_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable();
            $table->string('language_preference')->default('en');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stock_on_hands', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('shop_id');
            $table->integer('current_stock')->default(0);
            $table->text('remark')->nullable();
            $table->integer('status')->default(1);
            $table->integer('request_by')->nullable();
            $table->string('request_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('shops')->insert([
            ['id' => 1, 'name' => 'Branch A'],
            ['id' => 2, 'name' => 'Branch B'],
        ]);

        DB::table('categories')->insert(['id' => 1, 'name' => 'Hair Care']);
        DB::table('uoms')->insert(['id' => 1, 'name' => 'Bottle']);

        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Shampoo Standard', 'cost' => 5.00, 'price' => 12.00, 'category_id' => 1, 'uom_id' => 1],
            ['id' => 2, 'name' => 'Conditioner Premium', 'cost' => 8.50, 'price' => 20.00, 'category_id' => 1, 'uom_id' => 1],
            ['id' => 3, 'name' => 'Hair Gel Zero Cost', 'cost' => 0.00, 'price' => 15.00, 'category_id' => 1, 'uom_id' => 1],
        ]);

        // ShopProduct override for Branch B on Product 1 (Special price $14 instead of $12)
        DB::table('shop_products')->insert([
            ['id' => 1, 'shop_id' => 2, 'product_id' => 1, 'price' => 14.00, 'status' => 1],
        ]);

        $this->actingAs(new User(['id' => 1, 'name' => 'Super Admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_stock_on_hand_accessors_calculate_total_cost_and_total_amount_correctly(): void
    {
        $soh = StockOnHand::create([
            'product_id' => 1,
            'shop_id' => 1,
            'current_stock' => 10,
        ]);

        $soh->load(['product', 'shopProducts']);

        // Unit Cost = 5.00, Total Cost = 10 * 5.00 = 50.00
        $this->assertEquals(5.00, $soh->unit_cost);
        $this->assertEquals('$5.00', $soh->unit_cost_title);
        $this->assertEquals(50.00, $soh->total_cost);
        $this->assertEquals('$50.00', $soh->total_cost_title);

        // Base Product Price = 12.00, Total Amount = 10 * 12.00 = 120.00
        $this->assertEquals(12.00, $soh->unit_price);
        $this->assertEquals('$12.00', $soh->unit_price_title);
        $this->assertEquals(120.00, $soh->total_amount);
        $this->assertEquals('$120.00', $soh->total_amount_title);

        // Backward-compatible alias
        $this->assertEquals(120.00, $soh->total_price);
        $this->assertEquals('$120.00', $soh->total_price_title);
    }

    public function test_stock_on_hand_uses_shop_product_price_override_when_available(): void
    {
        // Branch B (shop_id = 2) has custom shop price $14.00 for Product 1
        $soh = StockOnHand::create([
            'product_id' => 1,
            'shop_id' => 2,
            'current_stock' => 5,
        ]);

        $soh->load(['product', 'shopProducts']);

        $this->assertEquals(5.00, $soh->unit_cost);
        $this->assertEquals(25.00, $soh->total_cost);
        $this->assertEquals('$25.00', $soh->total_cost_title);

        // Custom Branch B price = 14.00 (instead of default 12.00)
        $this->assertEquals(14.00, $soh->unit_price);
        $this->assertEquals('$14.00', $soh->unit_price_title);
        $this->assertEquals(70.00, $soh->total_amount); // 5 * 14.00
        $this->assertEquals('$70.00', $soh->total_amount_title);
    }

    public function test_zero_and_negative_stock_valuation(): void
    {
        // Zero stock
        $zeroStock = StockOnHand::create([
            'product_id' => 2,
            'shop_id' => 1,
            'current_stock' => 0,
        ]);
        $zeroStock->load(['product', 'shopProducts']);

        $this->assertEquals(0.00, $zeroStock->total_cost);
        $this->assertEquals('$0.00', $zeroStock->total_cost_title);
        $this->assertEquals(0.00, $zeroStock->total_amount);
        $this->assertEquals('$0.00', $zeroStock->total_amount_title);

        // Negative stock (discrepancy)
        $negStock = StockOnHand::create([
            'product_id' => 1,
            'shop_id' => 1,
            'current_stock' => -3,
        ]);
        $negStock->load(['product', 'shopProducts']);

        $this->assertEquals(-15.00, $negStock->total_cost); // -3 * 5.00
        $this->assertEquals('-$15.00', $negStock->total_cost_title);
        $this->assertEquals(-36.00, $negStock->total_amount); // -3 * 12.00
        $this->assertEquals('-$36.00', $negStock->total_amount_title);
    }

    public function test_stock_on_hand_export_csv_contains_total_cost_and_total_amount_headers(): void
    {
        StockOnHand::create(['product_id' => 1, 'shop_id' => 1, 'current_stock' => 10]);

        $response = $this->get('/admin/stock-on-hand/export');
        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString('Total Cost', $content);
        $this->assertStringContainsString('Total Amount', $content);
        $this->assertStringContainsString('$50.00', $content);
        $this->assertStringContainsString('$120.00', $content);
    }
}
