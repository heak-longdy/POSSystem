<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffExpenseExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test tables; never touch production or dev data.
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staff_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('shops')->insert([
            ['id' => 1, 'name' => 'Main Branch', 'status' => 1, 'created_at' => now()],
            ['id' => 2, 'name' => 'Airport Branch', 'status' => 1, 'created_at' => now()],
        ]);

        DB::table('staffs')->insert([
            ['id' => 1, 'name' => 'Sokha Chan & សុខា', 'phone_number' => '012345678', 'email' => 'sokha@example.com', 'status' => 1, 'created_at' => now()],
            ['id' => 2, 'name' => 'Bopha Vong', 'phone_number' => '098765432', 'email' => 'bopha@example.com', 'status' => 1, 'created_at' => now()],
        ]);

        DB::table('staff_expenses')->insert([
            [
                'id' => 1,
                'staff_id' => 1,
                'shop_id' => 1,
                'type' => 'salary',
                'amount' => 350.00,
                'expense_date' => '2026-10-15',
                'description' => 'October Base Salary for Sokha',
                'created_by' => 1,
                'status' => 1,
                'created_at' => '2026-10-15 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'staff_id' => 2,
                'shop_id' => 2,
                'type' => 'bonus',
                'amount' => 50.00,
                'expense_date' => '2026-10-16',
                'description' => 'Sales performance bonus',
                'created_by' => 1,
                'status' => 1,
                'created_at' => '2026-10-16 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'staff_id' => 1,
                'shop_id' => 1,
                'type' => 'deduction',
                'amount' => 20.00,
                'expense_date' => '2026-10-17',
                'description' => 'Uniform cost deduction',
                'created_by' => 1,
                'status' => 2,
                'created_at' => '2026-10-17 11:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'staff_id' => 2,
                'shop_id' => 2,
                'type' => 'other',
                'amount' => 15.00,
                'expense_date' => '2026-10-18',
                'description' => 'Trashed medical expense',
                'created_by' => 1,
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
        $response = $this->getJson(route('admin-staff-expense-report'))->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('rows', $data);
        $this->assertCount(2, $data['rows']);
        $this->assertEquals([2, 1], array_column($data['rows'], 'id'));

        // 2. Disabled tab (status 2)
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(3, $data['rows'][0]['id']);

        // 3. Trashed tab (status 'trash')
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 'trash']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(4, $data['rows'][0]['id']);

        // 4. Search keyword by staff name
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'search' => 'Sokha']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals('Sokha Chan & សុខា', $data['rows'][0]['staff']['name']);

        // 5. Filter by type
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'type' => 'bonus']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 6. Filter by staff_id
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'staff_id' => 1]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(1, $data['rows'][0]['id']);

        // 7. Filter by shop_id
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'shop_id' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 8. Filter by date range
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'from_date' => '2026-10-16', 'to_date' => '2026-10-16']))->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['rows']);
        $this->assertEquals(2, $data['rows'][0]['id']);

        // 9. Non-matching search returns empty rows
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'search' => 'NonExistent']))->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['rows']);

        // 10. Page parameter does not paginate / truncate export
        $response = $this->getJson(route('admin-staff-expense-report', ['status' => 1, 'page' => 2]))->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['rows']);
    }

    public function test_csv_export_endpoint_matches_filters_and_includes_utf8_bom(): void
    {
        $response = $this->get(route('admin-staff-expense-export', ['status' => 1, 'search' => 'Sokha']))->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $lines = array_filter(explode("\n", trim(substr($content, 3))));
        $this->assertCount(2, $lines); // Header + 1 row
        $header = str_getcsv($lines[0]);
        $row = str_getcsv($lines[1]);

        $this->assertContains('Staff Name', $header);
        $this->assertContains('Shop', $header);
        $this->assertContains('Type', $header);
        $this->assertEquals('Sokha Chan & សុខា', $row[1]);
        $this->assertEquals('Main Branch', $row[2]);
    }

    public function test_permissions_gate_staff_expense_export_routes(): void
    {
        $this->app['auth']->forgetGuards();

        // Guest redirect
        $this->getJson(route('admin-staff-expense-report'))->assertRedirect();
        $this->get(route('admin-staff-expense-export'))->assertRedirect();

        // Unauthorized user without staff-expense-excel
        $this->actingAs(new User(['name' => 'Unauthorized user', 'role' => 'user', 'language_preference' => 'en']));
        $this->getJson(route('admin-staff-expense-report'))->assertForbidden();
        $this->get(route('admin-staff-expense-export'))->assertForbidden();
    }
}
