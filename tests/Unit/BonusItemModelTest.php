<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderDetail;
use PHPUnit\Framework\TestCase;

class BonusItemModelTest extends TestCase
{
    public function test_order_detail_is_bonus_helper(): void
    {
        $regular = new OrderDetail(['is_bonus' => false]);
        $bonus = new OrderDetail(['is_bonus' => true]);

        $this->assertFalse($regular->isBonus());
        $this->assertTrue($bonus->isBonus());
    }

    public function test_order_detail_fillable_and_cast(): void
    {
        $detail = new OrderDetail();
        $this->assertContains('is_bonus', $detail->getFillable());
        $this->assertSame('boolean', $detail->getCasts()['is_bonus'] ?? null);
    }

    public function test_order_detail_format_quantity_removes_unnecessary_trailing_zeros(): void
    {
        $this->assertSame('5', OrderDetail::formatQuantity(5.00));
        $this->assertSame('5', OrderDetail::formatQuantity('5.00'));
        $this->assertSame('5.5', OrderDetail::formatQuantity(5.50));
        $this->assertSame('5.5', OrderDetail::formatQuantity('5.50'));
        $this->assertSame('4.5', OrderDetail::formatQuantity(4.50));
        $this->assertSame('4.5', OrderDetail::formatQuantity('4.50'));
        $this->assertSame('4.25', OrderDetail::formatQuantity(4.25));
        $this->assertSame('4.25', OrderDetail::formatQuantity('4.25'));
        $this->assertSame('3.75', OrderDetail::formatQuantity(3.75));
        $this->assertSame('3.75', OrderDetail::formatQuantity('3.75'));
        $this->assertSame('1', OrderDetail::formatQuantity(1));
        $this->assertSame('0.5', OrderDetail::formatQuantity(0.5));
        $this->assertSame('0.25', OrderDetail::formatQuantity(0.25));
        $this->assertSame('0', OrderDetail::formatQuantity(0));
        $this->assertSame('0', OrderDetail::formatQuantity(null));

        // Test model attribute accessor formatted_qty
        $item1 = new OrderDetail(['qty' => 5.00]);
        $this->assertSame('5', $item1->formatted_qty);

        $item2 = new OrderDetail(['qty' => 5.50]);
        $this->assertSame('5.5', $item2->formatted_qty);

        $item3 = new OrderDetail(['qty' => 4.25]);
        $this->assertSame('4.25', $item3->formatted_qty);
    }
}

