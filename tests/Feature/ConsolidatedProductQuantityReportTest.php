<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Report\ConsolidatedProductQuantityReportController;
use App\Models\User;
use App\Models\OrderDetail;
use App\Services\OrderPricing;
use Carbon\Carbon;
use Database\Seeders\ConsolidatedProductQuantityPermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsolidatedProductQuantityReportTest extends TestCase
{
    private const URL = '/admin/report/consolidated-product-quantity';

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-27 12:00:00');
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        (new ConsolidatedProductQuantityPermissionSeeder())->run();
        foreach (['shops', 'categories', 'uoms', 'products'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->string('name'); $table->string('code')->nullable();
                $table->integer('category_id')->nullable(); $table->integer('uom_id')->nullable();
                $table->softDeletes();
            });
            DB::table($name)->insert([
                ['id' => 1, 'name' => 'Tea & ទឹកតែ', 'code' => 'P001', 'category_id' => 1, 'uom_id' => 1],
                ['id' => 2, 'name' => 'Coffee', 'code' => 'P002', 'category_id' => 2, 'uom_id' => 2],
            ]);
        }
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->integer('shop_id'); $table->integer('created_by');
            $table->string('invoice_number'); $table->string('payment_status')->nullable();
            $table->dateTime('order_date'); $table->softDeletes();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id(); $table->integer('order_id'); $table->integer('product_id')->nullable();
            $table->integer('service_id')->nullable(); $table->string('type')->nullable();
            $table->integer('qty')->nullable(); $table->softDeletes();
            $table->double('price')->nullable();
            $table->decimal('original_price', 15, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('pricing_method')->nullable();
            $table->double('product_discount')->nullable();
            $table->string('product_discount_type')->nullable();
        });
        foreach ([
            [1, 1, 1, 'Paid', '2026-10-01 00:00:00'],
            [2, 2, 2, 'Partial', '2026-10-27 23:59:59'],
            [3, 1, 1, 'Cancel', '2026-10-15 12:00:00'],
            [4, 1, 1, 'Pending', '2026-09-30 23:59:59'],
            [5, 1, 1, 'Paid', '2026-10-28 00:00:00'],
        ] as [$id, $shop, $owner, $status, $date]) {
            DB::table('orders')->insert(['id' => $id, 'shop_id' => $shop, 'created_by' => $owner,
                'invoice_number' => 'INV-' . $id, 'payment_status' => $status, 'order_date' => $date]);
        }
        foreach ([[1, 1, 2], [1, 1, 3], [1, 2, 4], [2, 1, 7], [3, 1, 100], [4, 1, 200], [5, 1, 300]] as [$order, $product, $qty]) {
            $this->line($order, $product, $qty);
        }
        $this->line(1, 1, 999, ['deleted_at' => '2026-10-02']);
        $this->line(1, 1, 888, ['type' => 'service', 'service_id' => 1]);
        $this->actingAs(new User(['name' => 'Report admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function line($order, $product, $qty, array $extra = []): void
    {
        DB::table('order_details')->insert(array_merge([
            'order_id' => $order, 'product_id' => $product, 'qty' => $qty, 'type' => 'product', 'price' => 2.25,
        ], $extra));
    }

    private function filters(array $extra = []): array
    {
        return array_merge(['from_date' => '2026-10-01', 'to_date' => '2026-10-27'], $extra);
    }

    private function export(array $extra = [])
    {
        return $this->getJson(self::URL . '/report?' . http_build_query($this->filters($extra)));
    }

    public function test_quantities_are_consolidated_and_order_totals_are_distinct(): void
    {
        $data = $this->export()->assertOk()->assertJsonCount(2, 'rows')->json();
        $this->assertEquals(['product_count' => 2, 'order_count' => 2, 'shop_count' => 2, 'total_quantity' => 16, 'total_amount' => 36], $data['summary']);
        $this->assertEquals(27, $data['rows'][0]['total_amount']);
        $this->assertEquals(9, $data['rows'][1]['total_amount']);
        $this->assertEquals(12, $data['rows'][0]['total_quantity']);
        $this->assertEquals(2, $data['rows'][0]['order_count']);
        $this->assertEquals(4, $data['rows'][1]['total_quantity']);
        $this->assertEquals(1, $data['rows'][1]['order_count']);
        DB::table('orders')->where('id', 2)->update(['deleted_at' => '2026-10-27']);
        $this->export()->assertJsonPath('summary.total_quantity', 9)->assertJsonPath('summary.total_amount', 20.25);
    }

    public function test_filters_defaults_empty_results_and_validation(): void
    {
        foreach ([['shop_id' => 2], ['search' => 'INV-2']] as $filters) {
            $this->export($filters)->assertOk()->assertJsonCount(1, 'rows')->assertJsonPath('summary.total_quantity', 7)
                ->assertJsonPath('summary.total_amount', 15.75);
        }
        foreach ([['category_id' => 2], ['product_id' => 2], ['search' => 'P002']] as $filters) {
            $this->export($filters)->assertOk()->assertJsonPath('summary.total_quantity', 4)->assertJsonPath('summary.total_amount', 9);
        }
        $this->export(['shop_id' => 1, 'category_id' => 1, 'product_id' => 1, 'search' => 'Tea & ទឹកតែ'])
            ->assertOk()->assertJsonPath('summary.total_quantity', 5)->assertJsonPath('summary.total_amount', 11.25);
        $this->getJson(self::URL . '/report')->assertOk()->assertJsonPath('summary.total_quantity', 7)->assertJsonPath('summary.total_amount', 15.75);
        $this->export(['product_id' => 1234])->assertOk()->assertJsonCount(0, 'rows')->assertJsonPath('summary.total_quantity', 0)
            ->assertJsonPath('summary.total_amount', 0);
        $this->export(['from_date' => 'bad'])->assertUnprocessable()->assertJsonValidationErrors('from_date');
        $this->export(['from_date' => '2026-10-28'])->assertUnprocessable()->assertJsonValidationErrors('to_date');
        $this->export(['product_id' => ['1']])->assertUnprocessable();
    }

    public function test_archived_products_and_legacy_product_lines_retain_quantities(): void
    {
        DB::table('products')->where('id', 1)->update(['deleted_at' => '2026-10-20']);
        $this->line(1, 1, null, ['type' => null]);
        $this->line(1, 1, 0);
        $this->export(['product_id' => 1])->assertOk()->assertJsonPath('rows.0.product_name', 'Tea & ទឹកតែ')
            ->assertJsonPath('summary.total_quantity', 13)->assertJsonPath('summary.total_amount', 29.25);
    }

    public function test_total_amount_matches_order_pricing_for_mixed_historical_unit_prices(): void
    {
        DB::table('order_details')->delete();
        $lines = [
            ['qty' => 2, 'price' => 9.30, 'original_price' => '9.30', 'unit_price' => '9.00', 'pricing_method' => OrderPricing::AUTOMATIC],
            // Automatic unit prices are authoritative even if old compatibility fields disagree.
            ['qty' => 3, 'price' => 999, 'original_price' => '9.30', 'unit_price' => '10.25', 'pricing_method' => OrderPricing::AUTOMATIC, 'product_discount' => 99, 'product_discount_type' => 'percent'],
            ['qty' => 3, 'price' => 1.15, 'product_discount' => 10, 'product_discount_type' => 'percent'],
            ['qty' => 1, 'price' => 1.15, 'product_discount' => 10, 'product_discount_type' => 'percent'],
            ['qty' => 2, 'price' => 5, 'product_discount' => 1.25, 'product_discount_type' => 'usd'],
            ['qty' => 2, 'price' => 5, 'product_discount' => 1.25, 'product_discount_type' => 'khr'],
            ['qty' => 2, 'price' => 5, 'product_discount' => 150, 'product_discount_type' => 'percent'],
            ['qty' => 2, 'price' => 5, 'product_discount' => 10, 'product_discount_type' => 'usd'],
            ['qty' => 2, 'price' => 5, 'product_discount' => 1, 'product_discount_type' => null],
            ['qty' => 2, 'price' => null],
            ['qty' => 2, 'price' => 5, 'original_price' => '5.00', 'unit_price' => '0.00', 'pricing_method' => OrderPricing::AUTOMATIC],
        ];
        $expected = 0;
        foreach ($lines as $index => $line) {
            $this->line($index % 2 + 1, 1, $line['qty'], $line);
            $expected += app(OrderPricing::class)->line(new OrderDetail($line + ['type' => 'product']))['total'];
        }
        $data = $this->export()->assertOk()->assertJsonCount(1, 'rows')->json();
        $this->assertEquals(77.90, round($expected, 2));
        $this->assertEquals(round($expected, 2), $data['rows'][0]['total_amount']);
        $this->assertEquals(round($expected, 2), $data['summary']['total_amount']);
        $this->assertEquals(array_sum(array_column($lines, 'qty')), $data['summary']['total_quantity']);
    }

    public function test_listing_export_and_summary_match_beyond_one_page(): void
    {
        for ($id = 3; $id <= 62; $id++) {
            DB::table('products')->insert(['id' => $id, 'name' => 'Product ' . $id, 'code' => 'P' . $id, 'category_id' => 1, 'uom_id' => 1]);
            $this->line(1, $id, 1);
        }
        $request = Request::create(self::URL, 'GET', $this->filters());
        $view = app(ConsolidatedProductQuantityReportController::class)->index($request)->getData();
        $this->assertSame(50, $view['rows']->count());
        $this->assertSame(62, $view['rows']->total());
        $data = $this->export(['page' => 2])->assertOk()->assertJsonCount(62, 'rows')->json();
        $this->assertEquals((array) $view['summary'], $data['summary']);
        $this->assertEquals(76, $data['summary']['total_quantity']);
        $this->assertEquals(171, $data['summary']['total_amount']);
        $this->assertEquals($data['summary']['total_amount'], array_sum(array_column($data['rows'], 'total_amount')));
        foreach ($view['rows'] as $row) {
            $exported = collect($data['rows'])->firstWhere('product_id', $row->product_id);
            $this->assertEquals($row->total_amount, $exported['total_amount']);
        }
        $this->assertEquals(2, $data['summary']['order_count']);
    }

    public function test_permissions_and_order_ownership_apply_to_export_and_listing(): void
    {
        $staff = new User(['name' => 'Staff', 'role' => 'staff', 'language_preference' => 'en']);
        $staff->id = 1;
        $this->actingAs($staff);
        $this->export()->assertForbidden();
        $this->get(self::URL)->assertForbidden();
        $permissions = DB::table('permissions')->pluck('id', 'name');
        DB::table('model_has_permissions')->insert(['permission_id' => $permissions['report-consolidated-product-quantity-view'], 'model_id' => 1, 'model_type' => User::class]);
        $staff->unsetRelation('permissions');
        $this->export()->assertForbidden();
        DB::table('model_has_permissions')->insert(['permission_id' => $permissions['report-consolidated-product-quantity-excel'], 'model_id' => 1, 'model_type' => User::class]);
        $staff->unsetRelation('permissions');
        $this->export()->assertOk()->assertJsonPath('summary.total_quantity', 9)->assertJsonPath('summary.total_amount', 20.25);
        $data = app(ConsolidatedProductQuantityReportController::class)->index(Request::create(self::URL, 'GET', $this->filters()))->getData();
        $this->assertEquals(9, $data['summary']->total_quantity);
        DB::table('model_has_permissions')->where('permission_id', $permissions['report-consolidated-product-quantity-view'])->delete();
        $staff->unsetRelation('permissions');
        $this->export()->assertForbidden();
        auth()->forgetGuards();
        $this->getJson(self::URL . '/report')->assertRedirect();
    }

    public function test_view_renders_in_both_languages_and_permissions_seed_idempotently(): void
    {
        (new ConsolidatedProductQuantityPermissionSeeder())->run();
        $this->assertEquals(2, DB::table('permissions')->where('name', 'like', 'report-consolidated-product-quantity-%')->count());
        $data = app(ConsolidatedProductQuantityReportController::class)->index(Request::create(self::URL, 'GET', $this->filters()))->getData();
        $source = file_get_contents(resource_path('admin/views/pages/report/consolidatedProductQuantity/index.blade.php'));
        $source = preg_replace('/^@(extends|section|stop).*$/m', '', $source);
        $source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $html = Blade::render($source, $data + ['errors' => new \Illuminate\Support\ViewErrorBag()]);
            $this->assertStringContainsString(__('consolidated_product_quantity.title'), $html);
            $this->assertStringContainsString(__('consolidated_product_quantity.total_amount'), $html);
            $this->assertStringContainsString('$27.00', $html);
            $this->assertStringContainsString('$36.00', $html);
            $this->assertStringContainsString('Tea &amp; ទឹកតែ', $html);
            $this->assertStringContainsString('exceljs.min.js', $html);
            $this->assertStringNotContainsString('consolidated_product_quantity.', $html);
        }
    }
}
