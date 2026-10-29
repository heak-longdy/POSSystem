<?php

namespace Tests\Unit;

use App\Models\OrderDetail;
use App\Services\OrderPricing;
use PHPUnit\Framework\TestCase;

class OrderUnitPriceNormalizationTest extends TestCase
{
    private OrderPricing $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = new OrderPricing();
    }

    public function test_usd_cents_accepts_english_and_khmer_decimal_formats(): void
    {
        // Standard English decimal format
        $this->assertSame(830, $this->pricing->usdCents('8.30'));
        $this->assertSame(830, $this->pricing->usdCents('8.3'));
        $this->assertSame(800, $this->pricing->usdCents('8.'));
        $this->assertSame(800, $this->pricing->usdCents('8'));
        $this->assertSame(29, $this->pricing->usdCents('0.29'));

        // Khmer keyboard layout comma-based decimal format
        $this->assertSame(830, $this->pricing->usdCents('8,30'));
        $this->assertSame(830, $this->pricing->usdCents('8,3'));
        $this->assertSame(800, $this->pricing->usdCents('8,'));
        $this->assertSame(29, $this->pricing->usdCents('0,29'));

        // Khmer unicode numerals
        $this->assertSame(830, $this->pricing->usdCents('៨,៣០'));
        $this->assertSame(830, $this->pricing->usdCents('៨.៣០'));
    }

    public function test_usd_cents_rejects_invalid_decimal_formats(): void
    {
        // More than 2 decimal places
        $this->assertNull($this->pricing->usdCents('8.300'));
        $this->assertNull($this->pricing->usdCents('8,300'));

        // Multiple separators or mixed
        $this->assertNull($this->pricing->usdCents('8,,30'));
        $this->assertNull($this->pricing->usdCents('8..30'));
        $this->assertNull($this->pricing->usdCents('8.,30'));
        $this->assertNull($this->pricing->usdCents('8,.30'));

        // Non-numeric or negative
        $this->assertNull($this->pricing->usdCents('-8.30'));
        $this->assertNull($this->pricing->usdCents('-8,30'));
        $this->assertNull($this->pricing->usdCents('invalid'));
        $this->assertNull($this->pricing->usdCents(''));
    }

    public function test_automatic_amounts_normalizes_comma_unit_price_to_standard_dot_format(): void
    {
        $amounts = $this->pricing->automaticAmounts('9.30', '8,30', 2);

        $this->assertNotNull($amounts);
        $this->assertSame('8.30', $amounts['unit_price'], 'Unit price must be normalized to standard dot notation');
        $this->assertSame('9.30', $amounts['original_price']);
        $this->assertEquals(9.3, $amounts['price']);
        $this->assertEquals(1.0, $amounts['product_discount']);
        $this->assertEquals(18.6, $amounts['subtotal']);
        $this->assertEquals(2.0, $amounts['discount']);
        $this->assertEquals(16.6, $amounts['total']);
    }

    public function test_automatic_amounts_calculates_correctly_when_price_is_equal_or_marked_up(): void
    {
        // Unit price equal to original price
        $same = $this->pricing->automaticAmounts('10.00', '10,00', 3);
        $this->assertNotNull($same);
        $this->assertSame('10.00', $same['unit_price']);
        $this->assertEquals(0.0, $same['product_discount']);
        $this->assertEquals(30.0, $same['total']);

        // Markup: Unit price greater than original price
        $markup = $this->pricing->automaticAmounts('10.00', '12,50', 2);
        $this->assertNotNull($markup);
        $this->assertSame('12.50', $markup['unit_price']);
        $this->assertEquals(12.5, $markup['price']);
        $this->assertEquals(0.0, $markup['product_discount']);
        $this->assertEquals(25.0, $markup['total']);
    }
}

