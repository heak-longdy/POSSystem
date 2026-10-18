<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        // Isolated SQLite fixtures; never migrate or modify the development database.
        foreach (['shops', 'products', 'categories', 'uoms', 'suppliers', 'customers', 'users', 'barbers'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('category_id')->nullable();
                $table->integer('uom_id')->nullable();
                $table->softDeletes();
            });
        }
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->double('price')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('stock_types', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('name');
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->double('total_price');
            $table->double('total_commission');
            $table->dateTime('order_date');
            $table->softDeletes();
        });
        foreach (['stock_ins', 'stock_outs', 'stock_transfers', 'stock_on_hands', 'stock_histories'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                foreach (['product_id', 'shop_id', 'from_shop_id', 'to_shop_id', 'supplier_id', 'request_by', 'to_id'] as $column) {
                    $table->integer($column)->nullable();
                }
                $table->integer('qty')->default(12);
                $table->integer('current_stock')->default(24);
                $table->string('status')->default('1');
                $table->string('type')->nullable();
                $table->string('request_by_type')->nullable();
                $table->string('remark')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        DB::table('shops')->insert([['id' => 1, 'name' => 'Central Shop'], ['id' => 2, 'name' => 'West Shop']]);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Beverages']);
        DB::table('uoms')->insert(['id' => 1, 'name' => 'Box']);
        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Green Tea & ទឹកតែ', 'category_id' => 1, 'uom_id' => 1],
            ['id' => 2, 'name' => 'Coffee', 'category_id' => 1, 'uom_id' => 1],
        ]);
        foreach (self::modules() as [$module, $table]) {
            for ($id = 1; $id <= 4; $id++) {
                DB::table($table)->insert([
                    'id' => $id, 'product_id' => $id === 2 ? 2 : 1,
                    'shop_id' => $id === 2 ? 2 : 1, 'from_shop_id' => $id === 2 ? 2 : 1, 'to_shop_id' => 2,
                    'status' => $module === 'stock-movement' ? ($id === 2 ? 'stock_out' : 'stock_in') : ($id === 3 ? '2' : '1'),
                    'created_at' => $id === 2 ? '2026-10-17 10:00:00' : '2026-10-18 10:00:00',
                    'deleted_at' => $id === 4 ? '2026-10-18 12:00:00' : null,
                ]);
            }
        }
        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public static function modules(): array
    {
        return [
            ['stock-in', 'stock_ins'], ['stock-out', 'stock_outs'], ['stock-transfer', 'stock_transfers'],
            ['stock-on-hand', 'stock_on_hands'], ['stock-movement', 'stock_histories'],
        ];
    }

    private function urlFor(string $module, string $action, array $filters = []): string
    {
        return '/admin/' . $module . '/' . $action . '?' . http_build_query($filters);
    }

    /** @dataProvider modules */
    public function test_report_and_csv_filters_preserve_rows_and_quantities(string $module): void
    {
        $unfiltered = in_array($module, ['stock-on-hand', 'stock-movement']) ? [4, 3, 2, 1] : [2, 1];
        $filtered = in_array($module, ['stock-on-hand', 'stock-movement']) ? [4, 3, 1] : [1];
        $cases = [
            [[], $unfiltered],
            [['search' => 'Coffee'], [2]],
            [['shop_id' => 1], $filtered],
            [['search' => 'Green Tea & ទឹកតែ', 'shop_id' => 1], $filtered],
            [['search' => 'no matching product'], []],
            [['search' => '', 'shop_id' => ''], $unfiltered],
        ];
        if (in_array($module, ['stock-in', 'stock-out', 'stock-transfer'])) {
            $cases[] = [['status' => 1], [2, 1]];
            $cases[] = [['status' => 2], [3]];
            $cases[] = [['status' => 'trash'], [4]];
        }
        if ($module === 'stock-movement') {
            $cases[] = [['status_type' => 'stock_out'], [2]];
            $cases[] = [['from_date' => '2026-10-17'], [2]];
            $cases[] = [['from_date' => '2026-10-17', 'to_date' => '2026-10-18'], $unfiltered];
            $cases[] = [['from_date' => '2026-10-18', 'to_date' => '2026-10-18', 'status_type' => 'stock_in'], $filtered];
        } else {
            $cases[] = [['date' => '2026-10-17'], [2]];
        }
        if ($module === 'stock-transfer') {
            $cases[] = [['shop_id' => 2], [2, 1]]; // Source OR destination shop.
        }
        foreach ($cases as [$filters, $ids]) {
            $report = $this->getJson($this->urlFor($module, 'report', $filters))->assertOk();
            $this->assertSame($ids, array_column($report->json(), 'id'), $module . json_encode($filters));
            foreach ($report->json() as $row) {
                $this->assertEquals($module === 'stock-on-hand' ? 24 : 12, $row[$module === 'stock-on-hand' ? 'current_stock' : 'qty']);
                $this->assertSame('Beverages', $row['category_title']);
                $this->assertSame('Box', $row['uom_title']);
            }
            $csv = $this->get($this->urlFor($module, 'export', $filters))->assertOk();
            $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));
            $contents = $csv->streamedContent();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $contents);
            $rows = array_map('str_getcsv', explode("\n", trim(substr($contents, 3))));
            $this->assertCount(count($ids) + 1, $rows);
            $this->assertCount($module === 'stock-on-hand' ? 10 : ($module === 'stock-movement' ? 10 : 11), $rows[0]);
            $this->assertSame(array_column($report->json(), 'product_title'), array_column(array_slice($rows, 1), 1));
        }
    }

    /** @dataProvider modules */
    public function test_export_routes_keep_authentication_and_permission_checks(string $module): void
    {
        $this->app['auth']->forgetGuards();
        foreach (['report', 'export'] as $action) {
            $this->getJson($this->urlFor($module, $action))->assertRedirect();
        }
        $this->actingAs(new User(['name' => 'No access', 'role' => 'staff', 'language_preference' => 'en']));
        foreach (['report', 'export'] as $action) {
            $this->getJson($this->urlFor($module, $action))->assertForbidden();
        }
    }

    public function test_diagnostic_detects_the_missing_routes_seen_in_production(): void
    {
        $this->artisan('inventory:check-export-routes')->assertSuccessful();
        $routes = new RouteCollection();
        foreach ($this->app['router']->getRoutes() as $route) {
            if ($route->getName() !== 'admin-stock-transfer-report') {
                $routes->add($route);
            }
        }
        $this->app['router']->setRoutes($routes);
        $this->artisan('inventory:check-export-routes')->assertFailed();
        $this->getJson('/admin/stock-transfer/report?status=1')->assertNotFound();
    }
}
