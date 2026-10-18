<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test tables; never touch production or dev data.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('uoms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->integer('category_id')->nullable();
            $table->integer('uom_id')->nullable();
            $table->double('cost')->nullable();
            $table->double('price')->nullable();
            $table->double('commission')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('1');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Beverages', 'created_at' => now()],
            ['id' => 2, 'name' => 'Snacks', 'created_at' => now()],
        ]);

        DB::table('uoms')->insert([
            ['id' => 1, 'name' => 'Can', 'created_at' => now()],
            ['id' => 2, 'name' => 'Box', 'created_at' => now()],
        ]);

        DB::table('products')->insert([
            [
                'id' => 1,
                'code' => 'P001',
                'name' => 'Green Tea & ទឹកតែ',
                'category_id' => 1,
                'uom_id' => 1,
                'cost' => 0.50,
                'price' => 1.25,
                'commission' => 0.10,
                'status' => '1',
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'code' => 'P002',
                'name' => 'Coffee Ice',
                'category_id' => 1,
                'uom_id' => 1,
                'cost' => 0.80,
                'price' => 1.50,
                'commission' => 0.15,
                'status' => '1',
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'code' => 'P003',
                'name' => 'Disabled Chips',
                'category_id' => 2,
                'uom_id' => 2,
                'cost' => 1.00,
                'price' => 2.00,
                'commission' => 0.20,
                'status' => '2',
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'code' => 'P004',
                'name' => 'Trashed Soda',
                'category_id' => 1,
                'uom_id' => 1,
                'cost' => 0.40,
                'price' => 0.90,
                'commission' => 0.05,
                'status' => '1',
                'created_at' => '2026-10-18 11:30:00',
                'deleted_at' => '2026-10-18 12:00:00',
            ],
        ]);

        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_report_endpoint_returns_json_matching_active_status_and_search_filters(): void
    {
        // 1. Default (status 1 active)
        $response = $this->getJson(route('admin-product-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-product-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-product-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by name
        $response = $this->getJson(route('admin-product-report', ['status' => 1, 'search' => 'Green Tea']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Green Tea & ទឹកតែ', $data['rows'][0]['name']);
        $this->assertEquals('Beverages', $data['rows'][0]['category_title']);
        $this->assertEquals('Can', $data['rows'][0]['uom_title']);

        // 5. Search keyword by code
        $response = $this->getJson(route('admin-product-report', ['status' => 1, 'search' => 'P002']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 6. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-product-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 7. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-product-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-product-export', ['status' => 1, 'search' => 'Green Tea']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Code', $header);
        $this->assertContains('Name', $header);
        $this->assertEquals('P001', $row[1]);
        $this->assertEquals('Green Tea & ទឹកតែ', $row[2]);
    }

    public function test_permissions_gate_product_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-product-report'))->assertRedirect();
        $this->get(route('admin-product-export'))->assertRedirect();

        // Unauthorized user without product-excel
        $this->actingAs(new User(['name' => 'Staff no permission', 'role' => 'staff', 'language_preference' => 'en']));
        $this->getJson(route('admin-product-report'))->assertForbidden();
        $this->get(route('admin-product-export'))->assertForbidden();
        $this->getJson(route('admin-report-product-report'))->assertForbidden();
    }
}
