<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test table; never touches production or dev data.
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('user')->default('admin');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('customers')->insert([
            [
                'id' => 1,
                'name' => 'Sokha Chan & សុខា',
                'phone' => '012345678',
                'address' => 'St 271, Boeng Tumpun, Phnom Penh',
                'user' => 'admin',
                'status' => 1,
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Bopha Vong',
                'phone' => '098765432',
                'address' => 'St 1986, Sen Sok',
                'user' => 'admin',
                'status' => 1,
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Disabled Dara',
                'phone' => '011223344',
                'address' => 'St 63, BKK1',
                'user' => 'admin',
                'status' => 2,
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Trashed Rith',
                'phone' => '077889900',
                'address' => 'Chroy Changvar',
                'user' => 'admin',
                'status' => 1,
                'created_at' => '2026-10-18 11:30:00',
                'deleted_at' => '2026-10-18 12:00:00',
            ],
        ]);

        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_report_endpoint_returns_json_matching_active_status_and_search_filters(): void
    {
        // 1. Default (status 1 active)
        $response = $this->getJson(route('admin-customer-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-customer-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-customer-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by name
        $response = $this->getJson(route('admin-customer-report', ['status' => 1, 'search' => 'Sokha']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Sokha Chan & សុខា', $data['rows'][0]['name']);
        $this->assertNotNull($data['rows'][0]['created_at']);

        // 5. Search keyword by phone
        $response = $this->getJson(route('admin-customer-report', ['status' => 1, 'search' => '098765432']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 6. Search keyword by address
        $response = $this->getJson(route('admin-customer-report', ['status' => 1, 'search' => 'Sen Sok']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 7. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-customer-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 8. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-customer-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);

        // 9. Standard named route admin-report-customer-report returns the same data
        $responseAlias = $this->getJson(route('admin-report-customer-report', ['status' => 1]))->assertOk();
        $this->assertEquals($data['rows'], $responseAlias->json('rows'));
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-customer-export', ['status' => 1, 'search' => 'Sokha']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Name', $header);
        $this->assertContains('Phone', $header);
        $this->assertEquals('Sokha Chan & សុខា', $row[1]);
        $this->assertEquals('012345678', $row[2]);
    }

    public function test_permissions_gate_customer_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-customer-report'))->assertRedirect();
        $this->get(route('admin-customer-export'))->assertRedirect();
        $this->getJson(route('admin-report-customer-report'))->assertRedirect();

        // Unauthorized user without customer-view
        $this->actingAs(new User(['name' => 'Staff no permission', 'role' => 'staff', 'language_preference' => 'en']));
        $this->getJson(route('admin-customer-report'))->assertForbidden();
        $this->get(route('admin-customer-export'))->assertForbidden();
        $this->getJson(route('admin-report-customer-report'))->assertForbidden();
    }
}
