<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PositionExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test table; never touches production or dev data.
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('title');
            $table->tinyInteger('status')->default(1);
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('positions')->insert([
            [
                'id' => 1,
                'title' => 'Senior Barista & អ្នកឆុងកាហ្វេ',
                'status' => 1,
                'order' => 10,
                'user' => 1,
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'title' => 'Cashier',
                'status' => 1,
                'order' => 20,
                'user' => 1,
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'title' => 'Disabled Manager',
                'status' => 2,
                'order' => 5,
                'user' => 1,
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'title' => 'Trashed Supervisor',
                'status' => 1,
                'order' => 1,
                'user' => 1,
                'created_at' => '2026-10-18 11:30:00',
                'deleted_at' => '2026-10-18 12:00:00',
            ],
        ]);

        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_report_endpoint_returns_json_matching_active_status_and_search_filters(): void
    {
        // 1. Default (status 1 active, sorted by order desc, then id desc)
        $response = $this->getJson(route('admin-position-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-position-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-position-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by title
        $response = $this->getJson(route('admin-position-report', ['status' => 1, 'search' => 'Barista']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Senior Barista & អ្នកឆុងកាហ្វេ', $data['rows'][0]['title']);

        // 5. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-position-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 6. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-position-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);

        // 7. Standard named route admin-report-position-report returns the same data
        $responseAlias = $this->getJson(route('admin-report-position-report', ['status' => 1]))->assertOk();
        $this->assertEquals($data['rows'], $responseAlias->json('rows'));
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-position-export', ['status' => 1, 'search' => 'Barista']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Title', $header);
        $this->assertEquals('Senior Barista & អ្នកឆុងកាហ្វេ', $row[1]);
    }

    public function test_permissions_gate_position_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-position-report'))->assertRedirect();
        $this->get(route('admin-position-export'))->assertRedirect();
        $this->getJson(route('admin-report-position-report'))->assertRedirect();

        // Unauthorized user without position-view
        $this->actingAs(new User(['name' => 'Staff no permission', 'role' => 'staff', 'language_preference' => 'en']));
        $this->getJson(route('admin-position-report'))->assertForbidden();
        $this->get(route('admin-position-export'))->assertForbidden();
        $this->getJson(route('admin-report-position-report'))->assertForbidden();
    }
}
