<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductCostPriceDisplayTest extends TestCase
{
    public function test_zero_cost_and_price_show_dollar_value_not_empty_string(): void
    {
        $product = new Product();
        $product->cost = 0;
        $product->price = 0;

        $this->assertSame('0.00 $', $product->CostUsdFor);
        $this->assertSame('0.00 $', $product->PriceUsdFor);
    }

    public function test_zero_string_and_zero_float_cost_and_price_format_correctly(): void
    {
        $product = new Product();
        $product->cost = '0';
        $product->price = 0.0;

        $this->assertSame('0.00 $', $product->CostUsdFor);
        $this->assertSame('0.00 $', $product->PriceUsdFor);
    }

    public function test_positive_cost_and_price_format_with_two_decimals_and_dollar(): void
    {
        $product = new Product();
        $product->cost = 10.5;
        $product->price = 25;

        $this->assertSame('10.50 $', $product->CostUsdFor);
        $this->assertSame('25.00 $', $product->PriceUsdFor);
    }

    public function test_null_cost_and_price_return_empty_string(): void
    {
        $product = new Product();
        $product->cost = null;
        $product->price = null;

        $this->assertSame('', $product->CostUsdFor);
        $this->assertSame('', $product->PriceUsdFor);
    }
}
