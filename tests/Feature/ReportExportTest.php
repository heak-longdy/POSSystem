<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-18 12:00:00');
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        // Only the test connection is used; no application migrations or live data.
        foreach (['shops', 'products', 'services', 'categories', 'uoms', 'suppliers', 'customers', 'users', 'barbers', 'staffs', 'positions'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                foreach (['name', 'phone', 'phone_number', 'address', 'title'] as $column) $table->string($column)->nullable();
                foreach (['category_id', 'uom_id', 'position_id'] as $column) $table->integer($column)->nullable();
                $table->softDeletes();
            });
            DB::table($name)->insert([
                ['id' => 1, 'name' => 'Target & ទឹកតែ', 'category_id' => 1, 'uom_id' => 1, 'position_id' => 1],
                ['id' => 2, 'name' => 'Other', 'category_id' => 2, 'uom_id' => 2, 'position_id' => 2],
            ]);
        }
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            foreach (['shop_id', 'customer_id', 'barber_id'] as $column) $table->integer($column);
            foreach (['total_price', 'total_discount', 'total_commission', 'paid_amount'] as $column) $table->double($column)->default(0);
            foreach (['invoice_number', 'payment_status', 'pay_way', 'remark'] as $column) $table->string($column)->nullable();
            $table->dateTime('order_date');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            foreach (['order_id', 'product_id', 'service_id'] as $column) $table->integer($column)->nullable();
            $table->string('type');
            $table->integer('qty');
            foreach (['price', 'product_discount', 'service_discount'] as $column) $table->double($column)->default(0);
            $table->softDeletes();
        });
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->string('payment_method');
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            foreach (['product_id', 'shop_id', 'request_by', 'to_id', 'qty', 'current_stock', 'stock_in', 'stock_out'] as $column) $table->integer($column)->nullable();
            foreach (['status', 'type', 'transfer_type', 'request_by_type', 'remark'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        Schema::create('stock_types', function (Blueprint $table) {
            $table->id(); $table->string('key'); $table->string('name');
        });
        Schema::create('staff_expenses', function (Blueprint $table) {
            $table->id();
            foreach (['staff_id', 'shop_id', 'created_by', 'status'] as $column) $table->integer($column);
            $table->string('type'); $table->double('amount'); $table->date('expense_date');
            $table->text('description'); $table->timestamps(); $table->softDeletes();
        });
        foreach ([1, 2] as $id) {
            $date = $id === 1 ? '2026-10-18' : '2026-09-15';
            DB::table('orders')->insert([
                'id' => $id, 'shop_id' => $id, 'customer_id' => $id, 'barber_id' => $id,
                'total_price' => 100, 'total_discount' => 20, 'paid_amount' => 60,
                'invoice_number' => 'INV-' . $id, 'payment_status' => 'Partial', 'pay_way' => 'Cash', 'order_date' => $date,
            ]);
            DB::table('order_details')->insert(['order_id' => $id, 'product_id' => $id, 'type' => 'product', 'qty' => 2, 'price' => 60, 'product_discount' => 20]);
            DB::table('stock_histories')->insert([
                'id' => $id, 'product_id' => $id, 'shop_id' => $id, 'request_by' => $id,
                'qty' => 8, 'current_stock' => 18, 'status' => 'stock_in', 'created_at' => $date . ' 10:00:00',
            ]);
            DB::table('staff_expenses')->insert([
                'id' => $id, 'staff_id' => $id, 'shop_id' => $id, 'created_by' => $id,
                'type' => 'Deduction', 'amount' => 5, 'expense_date' => $date, 'status' => 1, 'description' => 'Fixture',
            ]);
        }
        $this->actingAs(new User(['name' => 'Export admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public static function modules(): array
    {
        return [['sales'], ['order-transaction'], ['inventory-movement'], ['staff-expense']];
    }

    private function report(string $module, array $filters = [])
    {
        return $this->getJson('/admin/report/' . $module . '/report?' . http_build_query(array_merge([
            'view_mode' => 'daily', 'grouping' => 'ungrouped', 'from_date' => '2026-10-01', 'to_date' => '2026-10-18',
        ], $filters)));
    }

    /** @dataProvider modules */
    public function test_export_modes_preserve_data_and_calculations(string $module): void
    {
        foreach ([['daily', 'ungrouped'], ['daily', 'grouped'], ['monthly', 'grouped'], ['monthly', 'ungrouped']] as [$mode, $grouping]) {
            $data = $this->report($module, ['view_mode' => $mode, 'grouping' => $grouping, 'year' => 2026, 'from_month' => 10, 'to_month' => 10])
                ->assertOk()->assertJsonCount(1, 'rows')->json();
            $row = $data['rows'][0];
            $ungrouped = $grouping === 'ungrouped' && !($mode === 'monthly' && in_array($module, ['sales', 'order-transaction']));
            if (in_array($module, ['sales', 'order-transaction'])) {
                $this->assertEquals(100, $row[$ungrouped ? 'total_price' : 'net_sales']);
                $this->assertEquals(120, $row['gross_sales']);
                $this->assertEquals(40, $row['remaining_amount']);
            } elseif ($module === 'inventory-movement') {
                $this->assertEquals(8, $row[$ungrouped ? 'qty' : 'net_movement']);
            } else {
                $this->assertEquals($ungrouped ? 5 : -5, $row[$ungrouped ? 'amount' : 'net_total']);
                if ($ungrouped) $this->assertTrue($row['is_deduction']);
            }
        }
    }

    /** @dataProvider modules */
    public function test_filters_empty_results_and_date_boundaries(string $module): void
    {
        $filters = ['shop_id' => 1, 'search' => 'Target & ទឹកតែ'];
        if (in_array($module, ['sales', 'order-transaction'])) {
            $filters += ['barber_id' => 1, 'customer_id' => 1, 'payment_status' => 'Partial', 'pay_way' => 'Cash', 'item_type' => 'product'];
        } elseif ($module === 'inventory-movement') {
            $filters += ['category_id' => 1, 'product_id' => 1, 'movement_type' => 'stock_in', 'request_by' => 1];
        } else {
            $filters += ['staff_id' => 1, 'type' => 'Deduction', 'status' => 1];
        }
        foreach ($filters as $key => $value) $this->report($module, [$key => $value])->assertOk()->assertJsonCount(1, 'rows');
        $this->report($module, $filters)->assertOk()->assertJsonPath('rows.0.id', 1);
        $this->report($module, ['from_date' => '2026-10-18', 'to_date' => '2026-10-18'])->assertOk()->assertJsonCount(1, 'rows');
        $this->report($module, ['to_date' => '2026-10-17'])->assertOk()->assertJsonCount(0, 'rows');
        $this->report($module, ['preset' => 'today'])->assertOk()->assertJsonCount(1, 'rows');
        $this->report($module, ['preset' => 'yesterday'])->assertOk()->assertJsonCount(0, 'rows');
        $this->report($module, ['view_mode' => 'monthly', 'grouping' => 'grouped', 'year' => 2026, 'from_month' => 9, 'to_month' => 10])
            ->assertOk()->assertJsonCount(2, 'rows');
        $this->report($module, ['search' => 'not present'])->assertOk()->assertJsonCount(0, 'rows');
        $this->report($module, ['search' => '', 'shop_id' => ''])->assertOk()->assertJsonCount(1, 'rows');
    }

    /** @dataProvider modules */
    public function test_routes_preserve_authentication_and_export_permissions(string $module): void
    {
        $this->app['auth']->forgetGuards();
        $this->report($module)->assertRedirect();
        $this->actingAs(new User(['name' => 'No permission', 'role' => 'staff', 'language_preference' => 'en']));
        $this->report($module)->assertForbidden();
    }
}
