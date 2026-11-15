<?php

namespace Tests\Unit;

use App\Models\StockOut;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StockOutQuantityDisplayTest extends TestCase
{
    public function test_stock_out_model_formatted_qty_accessor(): void
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
        ];

        foreach ($cases as $input => $expected) {
            $stockOut = new StockOut(['qty' => $input]);
            $this->assertSame($expected, $stockOut->formatted_qty, "Failed for input {$input}");
        }
    }

    public function test_listing_data_component_renders_stock_out_quantities_without_trailing_zeros(): void
    {
        $schema = \Illuminate\Support\Facades\Schema::connection('sqlite');
        if (!$schema->hasTable('permissions')) {
            $schema->create('permissions', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
            });
        }

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
            $stockOut = new StockOut(['qty' => $input]);
            $stockOut->id = 1;

            $items = collect([$stockOut]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $tbHeader = [
                ['field' => 'formatted_qty', 'title' => 'Quantity', 'class' => '', 'colVal' => 6],
            ];

            $html = view('admin::components.listingData', [
                'routeName' => 'stock-out',
                'createName' => 'Add Stock Out',
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

    public function test_stock_out_table_blade_renders_quantities_without_trailing_zeros(): void
    {
        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$input, $expected]) {
            $stockOut = new StockOut(['qty' => $input]);
            $stockOut->id = 1;

            $items = collect([$stockOut]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $html = view('admin::pages.inventoryManagement.stockOut.table', [
                'data' => $paginator,
            ])->render();

            $this->assertStringContainsString("<span>{$expected}</span>", $html, "Failed for table qty {$input}");
        }
    }
}
