<?php

namespace Tests\Unit;

use App\Models\StockOnHand;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StockOnHandQuantityDisplayTest extends TestCase
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

    public function test_stock_on_hand_model_formatted_current_stock_accessor(): void
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
            $stockOnHand = new StockOnHand(['current_stock' => $input]);
            $this->assertSame($expected, $stockOnHand->formatted_current_stock, "Failed for input {$input}");
        }
    }

    public function test_listing_data_component_renders_stock_on_hand_quantities_without_trailing_zeros(): void
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
            $stockOnHand = new StockOnHand(['current_stock' => $input]);
            $stockOnHand->id = 1;
            $stockOnHand->setRelation('shopProducts', collect());

            $items = collect([$stockOnHand]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $tbHeader = [
                ['field' => 'formatted_current_stock', 'title' => 'Current Stock', 'class' => '', 'colVal' => 9],
            ];

            $html = view('admin::components.listingData', [
                'routeName' => 'stock-on-hand',
                'createName' => '',
                'filterStatus' => false,
                'data' => $paginator,
                'status' => 1,
                'tbHeader' => $tbHeader,
                'showCreate' => false,
                'showSearch' => false,
                'showTabs' => false,
            ])->render();

            $this->assertStringContainsString("<span>{$expected}</span>", $html, "Failed for stock {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("<span>{$input}</span>", $html);
            }
        }
    }

    public function test_stock_on_hand_table_blade_renders_quantities_without_trailing_zeros(): void
    {
        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$input, $expected]) {
            $stockOnHand = new StockOnHand(['current_stock' => $input]);
            $stockOnHand->id = 1;
            $stockOnHand->setRelation('shopProducts', collect());

            $items = collect([$stockOnHand]);
            $paginator = new LengthAwarePaginator($items, 1, 50, 1);

            $html = view('admin::pages.inventoryManagement.stockOnHand.table', [
                'data' => $paginator,
            ])->render();

            $this->assertStringContainsString("<span>{$expected}</span>", $html, "Failed for stock {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("<span>{$input}</span>", $html);
            }
        }
    }

    public function test_stock_on_hand_view_blade_renders_quantities_without_trailing_zeros(): void
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
            $stockOnHand = new StockOnHand(['current_stock' => $input]);
            $stockOnHand->id = 1;
            $stockOnHand->setRelation('shopProducts', collect());

            $html = view('admin::pages.inventoryManagement.stockOnHand.view', [
                'id' => 1,
                'data' => $stockOnHand,
                'routeName' => 'stock-on-hand',
            ])->render();

            $this->assertStringContainsString("value=\"{$expected}\"", $html, "Failed for view input {$input}");
            if ($input !== $expected) {
                $this->assertStringNotContainsString("value=\"{$input}\"", $html);
            }
        }
    }
}
