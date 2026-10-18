<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test table; never touches production or dev data.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('profile')->nullable();
            $table->string('image')->nullable();
            $table->string('gender')->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 100)->unique()->nullable();
            $table->string('emergency_phone', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('role')->nullable();
            $table->string('type')->nullable();
            $table->string('language_preference')->default('en');
            $table->longText('des')->nullable();
            $table->longText('address')->nullable();
            $table->string('map')->nullable();
            $table->integer('user_id')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Super Admin User',
                'role' => 'super_admin',
                'status' => 1,
                'email' => 'super@example.com',
                'phone' => '011111111',
                'language_preference' => 'en',
                'created_at' => '2026-10-18 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Alice Admin & អាលីស',
                'role' => 'admin',
                'status' => 1,
                'email' => 'alice@example.com',
                'phone' => '012222222',
                'language_preference' => 'en',
                'created_at' => '2026-10-18 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Bob Cashier',
                'role' => 'cashier',
                'status' => 1,
                'email' => 'bob@example.com',
                'phone' => '013333333',
                'language_preference' => 'km',
                'created_at' => '2026-10-18 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Charlie Disabled Manager',
                'role' => 'manager',
                'status' => 2,
                'email' => 'charlie@example.com',
                'phone' => '014444444',
                'language_preference' => 'en',
                'created_at' => '2026-10-18 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'name' => 'David Trashed Staff',
                'role' => 'staff',
                'status' => 1,
                'email' => 'david@example.com',
                'phone' => '015555555',
                'language_preference' => 'en',
                'created_at' => '2026-10-18 11:30:00',
                'deleted_at' => '2026-10-18 12:00:00',
            ],
        ]);

        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    public function test_report_endpoint_returns_json_matching_active_status_and_filters(): void
    {
        // 1. Default (status 1 active) - super_admin is excluded, ordered desc by created_at
        $response = $this->getJson(route('admin-user-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([3, 2], array_column($data['rows'], 'id'));

        // created_at must be visible in report rows
        $this->assertArrayHasKey('created_at', $data['rows'][0]);
        $this->assertNotNull($data['rows'][0]['created_at']);

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-user-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-user-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(5, $data['rows'][0]['id']);

        // 4. Search keyword by name
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'search' => 'Alice']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Alice Admin & អាលីស', $data['rows'][0]['name']);

        // 5. Search keyword by email
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'search' => 'bob@example.com']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 6. Search keyword by phone
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'search' => '013333333']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 7. Filter by role
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'role' => 'cashier']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 8. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 9. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-user-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);

        // 10. Standard named route admin-report-user-report returns the same data
        $responseAlias = $this->getJson(route('admin-report-user-report', ['status' => 1]))->assertOk();
        $this->assertEquals($data['rows'], $responseAlias->json('rows'));
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-user-export', ['status' => 1, 'search' => 'Alice']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Name', $header);
        $this->assertContains('Email', $header);
        $this->assertContains('Role', $header);
        $this->assertEquals('Alice Admin & អាលីស', $row[1]);
        $this->assertEquals('alice@example.com', $row[2]);
    }

    public function test_permissions_gate_user_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-user-report'))->assertRedirect();
        $this->get(route('admin-user-export'))->assertRedirect();
        $this->getJson(route('admin-report-user-report'))->assertRedirect();

        // Unauthorized user without user-view
        $this->actingAs(new User(['name' => 'Unauthorized user', 'role' => 'guest_role', 'language_preference' => 'en']));
        $this->getJson(route('admin-user-report'))->assertForbidden();
        $this->get(route('admin-user-export'))->assertForbidden();
        $this->getJson(route('admin-report-user-report'))->assertForbidden();
    }
}
