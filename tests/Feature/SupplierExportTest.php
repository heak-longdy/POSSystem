<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplierExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test table; never touches production or dev data.
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('ordering')->nullable();
            $table->text('image')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'name' => 'Angkor Beverage & អ្នកផ្គត់ផ្គង់ភេសជ្ជៈ',
                'ordering' => 10,
                'user_id' => 1,
                'status' => 1,
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Phnom Penh Packaging',
                'ordering' => 20,
                'user_id' => 1,
                'status' => 1,
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Disabled Supplier Dairy',
                'ordering' => 30,
                'user_id' => 1,
                'status' => 2,
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Trashed Supplier Fruit',
                'ordering' => 40,
                'user_id' => 1,
                'status' => 1,
                'created_at' => '2026-10-18 11:30:00',
                'deleted_at' => '2026-10-18 12:00:00',
            ],
        ]);

        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_report_endpoint_returns_json_matching_active_status_and_search_filters(): void
    {
        // 1. Default (status 1 active) - ordered desc by id
        $response = $this->getJson(route('admin-supplier-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-supplier-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-supplier-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by name
        $response = $this->getJson(route('admin-supplier-report', ['status' => 1, 'search' => 'Angkor']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Angkor Beverage & អ្នកផ្គត់ផ្គង់ភេសជ្ជៈ', $data['rows'][0]['name']);

        // 5. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-supplier-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 6. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-supplier-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);

        // 7. Standard named route admin-report-supplier-report returns the same data
        $responseAlias = $this->getJson(route('admin-report-supplier-report', ['status' => 1]))->assertOk();
        $this->assertEquals($data['rows'], $responseAlias->json('rows'));
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-supplier-export', ['status' => 1, 'search' => 'Angkor']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Name', $header);
        $this->assertContains('Ordering', $header);
        $this->assertEquals('Angkor Beverage & អ្នកផ្គត់ផ្គង់ភេសជ្ជៈ', $row[1]);
        $this->assertEquals('10', $row[2]);
    }

    public function test_permissions_gate_supplier_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-supplier-report'))->assertRedirect();
        $this->get(route('admin-supplier-export'))->assertRedirect();
        $this->getJson(route('admin-report-supplier-report'))->assertRedirect();

        // Unauthorized user without supplier-view
        $this->actingAs(new User(['name' => 'Unauthorized user', 'role' => 'user', 'language_preference' => 'en']));
        $this->getJson(route('admin-supplier-report'))->assertForbidden();
        $this->get(route('admin-supplier-export'))->assertForbidden();
        $this->getJson(route('admin-report-supplier-report'))->assertForbidden();
    }
}
