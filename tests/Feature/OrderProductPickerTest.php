<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SelectController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderProductPickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->softDeletes();
        });
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('product_id');
            $table->double('price')->default(10);
            $table->double('discount')->default(2);
            $table->string('type')->default('usd');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        for ($id = 1; $id <= 26; $id++) {
            DB::table('products')->insert(['id' => $id, 'name' => "Product $id"]);
            DB::table('shop_products')->insert([
                'product_id' => $id, 'shop_id' => $id <= 25 ? 1 : 2,
                'created_at' => '2026-01-01 00:00:00',
                'from_date' => '2020-01-01', 'to_date' => '2020-01-02',
            ]);
        }
    }

    private function products(array $query): array
    {
        $request = Request::create('/admin/select/product', 'GET', $query + ['type' => 'product', 'shop_id' => 1]);
        $this->app->instance('request', $request);

        return (new SelectController())->selectProduct($request)->getData(true);
    }

    public function test_picker_pages_cover_the_shop_without_repeats_and_keep_promotion_rules(): void
    {
        $first = $this->products(['page' => 1]);
        $second = $this->products(['page' => 2]);
        $third = $this->products(['page' => 3]);
        $this->assertTrue($first['has_more']);
        $this->assertTrue($second['has_more']);
        $this->assertFalse($third['has_more']);
        $this->assertSame(25, $first['total']);
        $this->assertSame(3, $first['last_page']);
        $this->assertSame(1, $first['current_page']);
        $this->assertSame(1, $first['from']);
        $this->assertSame(12, $first['to']);
        $this->assertSame(12, $first['per_page']);
        $this->assertSame(25, $second['total']);
        $this->assertSame(2, $second['current_page']);
        $this->assertSame(13, $second['from']);
        $this->assertSame(24, $second['to']);
        $this->assertSame(25, $third['total']);
        $this->assertSame(3, $third['current_page']);
        $this->assertSame(25, $third['from']);
        $this->assertSame(25, $third['to']);
        $this->assertCount(12, $first['data']);
        $this->assertCount(12, $second['data']);
        $this->assertCount(1, $third['data']);
        $items = array_merge($first['data'], $second['data'], $third['data']);
        $this->assertSame(range(25, 1), array_column($items, 'product_id'));
        foreach ($items as $item) {
            $this->assertEquals(1, $item['shop_id']);
            $this->assertEquals(10, $item['price']);
            $this->assertEquals(0, $item['discount'], 'Expired promotions remain disabled.');
        }
    }

    public function test_search_shop_filter_and_existing_catalog_response_are_preserved(): void
    {
        $legacy = $this->products([]);
        $this->assertCount(12, $legacy['data']);
        $this->assertArrayNotHasKey('has_more', $legacy);
        $search = $this->products(['page' => 1, 'search' => 'Product 25']);
        $this->assertSame([25], array_column($search['data'], 'product_id'));
        $otherShop = $this->products(['page' => 1, 'shop_id' => 2]);
        $this->assertSame([26], array_column($otherShop['data'], 'product_id'));
        $empty = $this->products(['page' => 1, 'shop_id' => 3]);
        $this->assertSame([], $empty['data']);
        $this->assertFalse($empty['has_more']);
    }
}
