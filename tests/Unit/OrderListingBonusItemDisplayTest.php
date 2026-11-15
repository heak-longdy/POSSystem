<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Tests\TestCase;

class OrderListingBonusItemDisplayTest extends TestCase
{
    public function test_regular_product_renders_with_standard_package_icon_and_no_bonus_badge(): void
    {
        $order = new Order(['id' => 101, 'invoice_number' => 'NO-001']);
        $product = new Product(['name' => 'Ice Green Tea 485ml']);
        $detail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 2,
            'price' => 2.50,
        ]);
        $detail->setRelation('product', $product);
        $order->setRelation('orderDetails', collect([$detail]));

        $html = OrderController::renderOrderItems($order);

        $this->assertStringContainsString('order-table-item', $html);
        $this->assertStringNotContainsString('order-table-item--bonus', $html);
        $this->assertStringContainsString('bx-package order-item-icon', $html);
        $this->assertStringNotContainsString('bx-gift', $html);
        $this->assertStringNotContainsString('order-item-bonus-badge', $html);
        $this->assertStringContainsString('Ice Green Tea 485ml', $html);
        $this->assertStringContainsString('×2', $html);
    }

    public function test_bonus_product_renders_with_gift_icon_and_compact_bonus_badge(): void
    {
        $order = new Order(['id' => 102, 'invoice_number' => 'NO-0567']);
        $product = new Product(['name' => 'Fanta Big 2.25L']);
        $detail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 1,
            'price' => 0.00,
        ]);
        $detail->setRelation('product', $product);
        $order->setRelation('orderDetails', collect([$detail]));

        $html = OrderController::renderOrderItems($order);

        $this->assertStringContainsString('order-table-item--bonus', $html);
        $this->assertStringContainsString('bx-gift order-item-icon order-item-icon--bonus', $html);
        $this->assertStringContainsString('order-item-bonus-badge', $html);
        $this->assertStringContainsString('Fanta Big 2.25L', $html);
        $this->assertStringContainsString('×1', $html);
    }

    public function test_mixed_order_displays_both_regular_and_bonus_products_correctly(): void
    {
        $order = new Order(['id' => 103, 'invoice_number' => 'NO-003']);
        $regProduct = new Product(['name' => 'Coca Cola 330ml']);
        $regDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 5,
            'price' => 1.50,
        ]);
        $regDetail->setRelation('product', $regProduct);

        $bonusProduct = new Product(['name' => 'Free Glass Tumbler']);
        $bonusDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 1,
            'price' => 0.00,
        ]);
        $bonusDetail->setRelation('product', $bonusProduct);

        $order->setRelation('orderDetails', collect([$regDetail, $bonusDetail]));

        $html = OrderController::renderOrderItems($order);

        // Regular item assertions
        $this->assertStringContainsString('Coca Cola 330ml', $html);
        $this->assertStringContainsString('bx-package order-item-icon', $html);
        $this->assertStringContainsString('×5', $html);

        // Bonus item assertions
        $this->assertStringContainsString('Free Glass Tumbler', $html);
        $this->assertStringContainsString('order-table-item--bonus', $html);
        $this->assertStringContainsString('bx-gift order-item-icon order-item-icon--bonus', $html);
        $this->assertStringContainsString('order-item-bonus-badge', $html);
        $this->assertStringContainsString('×1', $html);
    }

    public function test_multiple_products_order_with_bonus_in_expanded_list_and_popover(): void
    {
        $order = new Order(['id' => 104, 'invoice_number' => 'NO-004']);

        $p1 = new Product(['name' => 'Product 1']);
        $d1 = new OrderDetail(['type' => 'product', 'is_bonus' => false, 'qty' => 1]);
        $d1->setRelation('product', $p1);

        $p2 = new Product(['name' => 'Product 2']);
        $d2 = new OrderDetail(['type' => 'product', 'is_bonus' => false, 'qty' => 2]);
        $d2->setRelation('product', $p2);

        $p3 = new Product(['name' => 'Hidden Bonus Item 3']);
        $d3 = new OrderDetail(['type' => 'product', 'is_bonus' => true, 'qty' => 1]);
        $d3->setRelation('product', $p3);

        $order->setRelation('orderDetails', collect([$d1, $d2, $d3]));

        $html = OrderController::renderOrderItems($order);

        // Ensure more button exists
        $this->assertStringContainsString('order-items-more-btn', $html);
        $this->assertStringContainsString('order-table-items-extra', $html);
        $this->assertStringContainsString('order-items-hover-popover', $html);

        // The hidden bonus product should be present in both extra container and popover
        $this->assertStringContainsString('Hidden Bonus Item 3', $html);
        $this->assertStringContainsString('order-table-item--bonus', $html);
        $this->assertStringContainsString('order-item-bonus-badge', $html);
        $this->assertStringContainsString('bx-gift order-item-icon order-item-icon--bonus', $html);
    }

    public function test_bonus_product_with_decimal_quantity_renders_correctly(): void
    {
        $order = new Order(['id' => 105, 'invoice_number' => 'NO-005']);
        $product = new Product(['name' => 'Bonus Mineral Water 1.5L']);
        $detail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 0.5,
            'price' => 0.00,
        ]);
        $detail->setRelation('product', $product);
        $order->setRelation('orderDetails', collect([$detail]));

        $html = OrderController::renderOrderItems($order);

        $this->assertStringContainsString('Bonus Mineral Water 1.5L', $html);
        $this->assertStringContainsString('order-table-item--bonus', $html);
        $this->assertStringContainsString('×0.5', $html);
    }

    public function test_bonus_product_quantities_format_without_trailing_zeros(): void
    {
        $cases = [
            '5.00' => '×5',
            '5.50' => '×5.5',
            '4.50' => '×4.5',
            '4.25' => '×4.25',
            '3.75' => '×3.75',
        ];

        foreach ($cases as $qtyInput => $expectedDisplay) {
            $order = new Order(['id' => 106, 'invoice_number' => 'NO-006']);
            $product = new Product(['name' => 'Bonus Item']);
            $detail = new OrderDetail([
                'type' => 'product',
                'is_bonus' => true,
                'qty' => $qtyInput,
                'price' => 0.00,
            ]);
            $detail->setRelation('product', $product);
            $order->setRelation('orderDetails', collect([$detail]));

            $html = OrderController::renderOrderItems($order);
            $this->assertStringContainsString($expectedDisplay, $html, "Failed for qty {$qtyInput}");
        }
    }
}
