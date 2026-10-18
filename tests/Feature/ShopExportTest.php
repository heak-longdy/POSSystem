<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test table; never touches production or dev data.
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

        DB::table('shops')->insert([
            [
                'id' => 1,
                'name' => 'Downtown Central & សាខាកណ្តាល',
                'nick_name' => 'DC-01',
                'phone' => '012345678',
                'address' => 'Building 10, St 214, Phnom Penh',
                'status' => 1,
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Toul Kork Branch',
                'nick_name' => 'TK-02',
                'phone' => '098765432',
                'address' => 'St 315, Toul Kork',
                'status' => 1,
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Disabled BKK Shop',
                'nick_name' => 'BKK-03',
                'phone' => '011223344',
                'address' => 'St 51, BKK1',
                'status' => 2,
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Trashed Sen Sok Shop',
                'nick_name' => 'SS-04',
                'phone' => '077889900',
                'address' => 'Phnom Penh Thmey, Sen Sok',
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
        $response = $this->getJson(route('admin-shop-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-shop-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-shop-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by name
        $response = $this->getJson(route('admin-shop-report', ['status' => 1, 'search' => 'Downtown']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Downtown Central & សាខាកណ្តាល', $data['rows'][0]['name']);

        // 5. Search keyword by phone
        $response = $this->getJson(route('admin-shop-report', ['status' => 1, 'search' => '098765432']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 6. Search keyword by address
        $response = $this->getJson(route('admin-shop-report', ['status' => 1, 'search' => 'Toul Kork']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 7. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-shop-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 8. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-shop-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);

        // 9. Standard named route admin-report-shop-report returns the same data
        $responseAlias = $this->getJson(route('admin-report-shop-report', ['status' => 1]))->assertOk();
        $this->assertEquals($data['rows'], $responseAlias->json('rows'));
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-shop-export', ['status' => 1, 'search' => 'Downtown']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Name', $header);
        $this->assertContains('Phone Number', $header);
        $this->assertEquals('Downtown Central & សាខាកណ្តាល', $row[1]);
        $this->assertEquals('012345678', $row[2]);
    }

    public function test_permissions_gate_shop_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-shop-report'))->assertRedirect();
        $this->get(route('admin-shop-export'))->assertRedirect();
        $this->getJson(route('admin-report-shop-report'))->assertRedirect();

        // Unauthorized user without shop-view
        $this->actingAs(new User(['name' => 'Staff no permission', 'role' => 'staff', 'language_preference' => 'en']));
        $this->getJson(route('admin-shop-report'))->assertForbidden();
        $this->get(route('admin-shop-export'))->assertForbidden();
        $this->getJson(route('admin-report-shop-report'))->assertForbidden();
    }
}
