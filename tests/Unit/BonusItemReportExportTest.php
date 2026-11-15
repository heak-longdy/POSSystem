<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Shop;
use App\Services\OrderPricing;
use Tests\TestCase;

class BonusItemReportExportTest extends TestCase
{
    public function test_excel_export_order_view_renders_bonus_items_with_bonus_tag_and_zero_price(): void
    {
        $order = new Order([
            'id' => 888,
            'invoice_number' => 'INV-EXCEL-001',
            'order_date' => '2026-11-15 11:00:00',
            'total_price' => 45.00,
            'total_discount' => 5.00,
            'total_commission' => 2.00,
            'paid_amount' => 45.00,
            'payment_status' => 'Paid',
            'payment_date' => '2026-11-15 11:30:00',
        ]);
        $order->setRelation('shop', new Shop(['name' => 'Flagship Shop']));
        $order->setRelation('customer', new Customer(['name' => 'Alice']));

        $regularProduct = new Product(['name' => 'Premium Shampoo', 'price' => 50.00]);
        $bonusProduct = new Product(['name' => 'Free Comb', 'price' => 10.00]);

        $regularDetail = new OrderDetail([
            'id' => 1,
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 1,
            'price' => 50.00,
            'product_discount' => 5.00,
            'product_commission' => 2.00,
            'point' => 5,
        ]);
        $regularDetail->setRelation('order', $order);
        $regularDetail->setRelation('product', $regularProduct);

        $bonusDetail = new OrderDetail([
            'id' => 2,
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 2,
            'price' => 0.00,
            'product_discount' => 0.00,
            'product_commission' => 0.00,
            'point' => 0,
        ]);
        $bonusDetail->setRelation('order', $order);
        $bonusDetail->setRelation('product', $bonusProduct);

        $html = view('admin::export.excelExportOrder', [
            'orderData' => collect([$regularDetail, $bonusDetail]),
        ])->render();

        // Regular item assertions
        $this->assertStringContainsString('Premium Shampoo', $html);
        $this->assertStringContainsString('$50.00', $html);
        $this->assertStringContainsString('$5.00', $html);

        // Bonus item assertions
        $this->assertStringContainsString('Free Comb (Bonus)', $html);
        $this->assertStringContainsString('$0.00', $html);
    }

    public function test_consolidated_product_quantity_report_line_amount_cents_is_zero_for_bonus(): void
    {
        $controller = new \App\Http\Controllers\Admin\Report\ConsolidatedProductQuantityReportController();
        $pricing = new OrderPricing();

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('lineAmountCents');
        $method->setAccessible(true);

        $bonusDetail = new OrderDetail([
            'is_bonus' => true,
            'qty' => 5,
            'price' => 100.00,
        ]);

        $cents = $method->invoke($controller, $bonusDetail, $pricing);
        $this->assertSame(0, $cents);

        $regularDetail = new OrderDetail([
            'is_bonus' => false,
            'pricing_method' => OrderPricing::AUTOMATIC,
            'qty' => 2,
            'price' => 15.00,
        ]);

        $centsRegular = $method->invoke($controller, $regularDetail, $pricing);
        $this->assertSame(3000, $centsRegular);
    }
}

