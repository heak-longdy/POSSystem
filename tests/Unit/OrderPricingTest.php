<?php

namespace Tests\Unit;

use App\Models\OrderDetail;
use App\Services\OrderPricing;
use PHPUnit\Framework\TestCase;

class OrderPricingTest extends TestCase
{
    public function test_order_pricing_matches_the_create_order_fixtures(): void
    {
        $pricing = new OrderPricing();
        $fixtures = json_decode(file_get_contents(__DIR__ . '/../Fixtures/order-pricing.json'), true);

        foreach ($fixtures as $fixture) {
            $details = [];
            foreach ($fixture['items'] as $index => $item) {
                $type = $item['product_type'];
                $data = $item['itemData'];
                $detail = new OrderDetail([
                    'type' => $type, 'price' => $data['price'], 'qty' => $item['product_qty'],
                    'original_price' => $data['originalPrice'] ?? null,
                    'unit_price' => $data['unitPrice'] ?? null,
                    'pricing_method' => $data['pricingMethod'] ?? null,
                    $type . '_discount' => $data['discount'] ?? 0,
                    $type . '_discount_type' => $data['discountType'] ?? null,
                    $type . '_commission' => $data['commission'] ?? 0,
                    $type . '_commission_type' => $data['commissionType'] ?? null,
                ]);
                $details[] = $detail;
                $this->assertEquals($fixture['lines'][$index], $pricing->line($detail)['total'], $fixture['name']);
            }

            $this->assertEquals([
                'subtotal' => $fixture['subtotal'], 'total_discount' => $fixture['discount'],
                'total_commission' => $fixture['commission'], 'total_price' => $fixture['total'],
            ], $pricing->totals($details), $fixture['name']);
        }
    }

    public function test_automatic_prices_are_parsed_as_cents_without_silent_rounding(): void
    {
        $pricing = new OrderPricing();
        foreach (['9' => 900, '9.' => 900, '9.00' => 900, '0.29' => 29, '0' => 0] as $value => $expected) {
            $this->assertSame($expected, $pricing->usdCents($value));
        }
        foreach (['8,30' => 830, '9,' => 900, '9,00' => 900, '0,29' => 29, '៨,៣០' => 830, '៨.៣០' => 830] as $value => $expected) {
            $this->assertSame($expected, $pricing->usdCents($value));
        }
        foreach (['', '-1', '9.001', '9,001', 'NaN', '1e2', '0.5oops', '10000000000000', INF, true, [], null, '8,,30', '8..30', '8.,30'] as $value) {
            $this->assertNull($pricing->usdCents($value));
        }
        $this->assertNull($pricing->automaticAmounts('9999999999999.99', '1', 2));
        $this->assertSame(999999999999999, $pricing->usdCents('9999999999999.99'));
        $this->assertSame('9999999999999.99', $pricing->usdString(OrderPricing::MAX_CENTS));

        // Comma-based decimal in automaticAmounts normalizes unit_price to standard dot format
        $amounts = $pricing->automaticAmounts('9.30', '8,30', 2);
        $this->assertNotNull($amounts);
        $this->assertSame('8.30', $amounts['unit_price']);
        $this->assertSame('9.30', $amounts['original_price']);
        $this->assertEquals(9.3, $amounts['price']);
        $this->assertEquals(1.0, $amounts['product_discount']);
        $this->assertEquals(18.6, $amounts['subtotal']);
        $this->assertEquals(2.0, $amounts['discount']);
        $this->assertEquals(16.6, $amounts['total']);
    }

    public function test_automatic_totals_ignore_stale_compatibility_discount_and_price(): void
    {
        $detail = new OrderDetail([
            'type' => 'product', 'qty' => 3, 'original_price' => '9.30', 'unit_price' => '9.00',
            'pricing_method' => OrderPricing::AUTOMATIC, 'price' => 999, 'product_discount' => 99,
            'product_discount_type' => 'percent', 'product_commission' => 10, 'product_commission_type' => 'percent',
        ]);
        $this->assertEquals(['subtotal' => 27.90, 'discount' => 0.90, 'commission' => 2.79, 'total' => 27],
            (new OrderPricing())->line($detail));
    }
}
