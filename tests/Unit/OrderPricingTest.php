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
}
