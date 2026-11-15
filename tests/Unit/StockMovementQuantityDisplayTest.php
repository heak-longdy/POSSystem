<?php

namespace Tests\Unit;

use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StockMovementQuantityDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $schema = \Illuminate\Support\Facades\Schema::connection('sqlite');
        if (!$schema->hasTable('permissions')) {
            $schema->create('permissions', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
            });
        }
    }

    public function test_stock_movement_model_formatted_qty_accessor(): void
    {
        $cases = [
            '5.00' => '5',
            '5.50' => '5.5',
            '4.50' => '4.5',
            '4.25' => '4.25',
            '3.75' => '3.75',
            '1.00' => '1',
            '0.50' => '0.5',
            '0.25' => '0.25',
            '0.00' => '0',
        ];

        foreach ($cases as $input => $expected) {
            $stockHistory = new StockHistory(['qty' => $input]);
            $this->assertSame($expected, $stockHistory->formatted_qty, "Failed for input {$input}");
        }
    }

    public function test_listing_data_component_renders_stock_movement_quantities_without_trailing_zeros(): void
    {
        $user = new User(['role' => 'super_admin']);
        $user->id = 1;
        Auth::login($user);

        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$input, $expected]) {
            $stockHistory = new StockHistory(['qty' => $input]);
            $stockHistory->id = 1;

            $items = collect([$stockHistory]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $tbHeader = [
                ['field' => 'formatted_qty', 'title' => 'Quantity', 'class' => '', 'colVal' => 8],
            ];

            $html = view('admin::components.listingData', [
                'routeName' => 'stock-movement',
                'createName' => '',
                'filterStatus' => false,
                'data' => $paginator,
                'status' => 1,
                'tbHeader' => $tbHeader,
                'showCreate' => false,
                'showSearch' => false,
                'showTabs' => false,
            ])->render();

            $this->assertStringContainsString("<span>{$expected}</span>", $html, "Failed for qty {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("<span>{$input}</span>", $html);
            }
        }
    }

    public function test_stock_movement_table_blade_renders_quantities_without_trailing_zeros(): void
    {
        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$input, $expected]) {
            $stockHistory = new StockHistory(['qty' => $input]);
            $stockHistory->id = 1;

            $items = collect([$stockHistory]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $html = view('admin::pages.inventoryManagement.stockMovement.table', [
                'data' => $paginator,
            ])->render();

            $this->assertStringContainsString("<span>{$expected}</span>", $html, "Failed for qty {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("<span>{$input}</span>", $html);
            }
        }
    }

    public function test_stock_movement_view_blade_renders_quantities_without_trailing_zeros(): void
    {
        $user = new User(['role' => 'super_admin']);
        $user->id = 1;
        Auth::login($user);

        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$input, $expected]) {
            $stockHistory = new StockHistory([
                'qty' => $input,
                'current_stock' => $input,
                'stock_in' => $input,
                'stock_out' => $input,
            ]);
            $stockHistory->id = 1;

            $html = view('admin::pages.inventoryManagement.stockMovement.view', [
                'id' => 1,
                'data' => $stockHistory,
                'routeName' => 'stock-movement',
            ])->render();

            $this->assertStringContainsString("value=\"{$expected}\"", $html, "Failed for view input {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("value=\"{$input}\"", $html);
            }
        }
    }
}
