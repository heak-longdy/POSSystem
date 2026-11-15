<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\OrderRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BonusItemOrderRequestTest extends TestCase
{
    public function test_validation_passes_with_regular_cart_only(): void
    {
        $request = new OrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'order_date' => '2026-11-15 10:00:00',
            'shop_id' => 1,
            'customer_id' => 1,
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => 1]]),
            'dataBonusCarts' => json_encode([]),
        ], [
            'dataCarts' => $rules['dataCarts'],
            'dataBonusCarts' => $rules['dataBonusCarts'],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_with_bonus_cart_only(): void
    {
        $request = new OrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'order_date' => '2026-11-15 10:00:00',
            'shop_id' => 1,
            'customer_id' => 1,
            'dataCarts' => null,
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => 3]]),
        ], [
            'dataCarts' => $rules['dataCarts'],
            'dataBonusCarts' => $rules['dataBonusCarts'],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_when_both_regular_and_bonus_carts_are_absent(): void
    {
        $request = new OrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'order_date' => '2026-11-15 10:00:00',
            'shop_id' => 1,
            'customer_id' => 1,
            'dataCarts' => null,
            'dataBonusCarts' => null,
        ], [
            'dataCarts' => $rules['dataCarts'],
            'dataBonusCarts' => $rules['dataBonusCarts'],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dataCarts', $validator->errors()->messages());
    }

    public function test_decode_cart_throws_data_bonus_carts_error_for_invalid_bonus_quantity(): void
    {
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $refMethod = new \ReflectionMethod($controller, 'decodeCart');
        $refMethod->setAccessible(true);

        $request = new OrderRequest();
        $request->merge([
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => 1]]),
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => 0]]),
        ]);

        try {
            $refMethod->invoke($controller, $request);
            $this->fail('Expected ValidationException was not thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('dataBonusCarts', $e->errors());
            $this->assertArrayNotHasKey('dataCarts', $e->errors());
        }
    }

    public function test_decode_cart_throws_data_carts_error_for_invalid_regular_quantity(): void
    {
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $refMethod = new \ReflectionMethod($controller, 'decodeCart');
        $refMethod->setAccessible(true);

        $request = new OrderRequest();
        $request->merge([
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => -1]]),
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => 1]]),
        ]);

        try {
            $refMethod->invoke($controller, $request);
            $this->fail('Expected ValidationException was not thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('dataCarts', $e->errors());
            $this->assertArrayNotHasKey('dataBonusCarts', $e->errors());
        }
    }

    public function test_create_order_view_has_bonus_section_validation_markup(): void
    {
        $content = file_get_contents(resource_path('admin/views/pages/order/createOrder.blade.php'));

        $this->assertStringContainsString('bonus-error-summary', $content);
        $this->assertStringContainsString('bonusValidationMessages()', $content);
        $this->assertStringContainsString('hasBonusQuantityValidationError(item)', $content);
        $this->assertStringContainsString('bonusQtyRealTimeAction(item)', $content);
        $this->assertStringNotContainsString('bonus-qty-error-text', $content);
        $this->assertStringContainsString('validBonusQty', $content);
        $this->assertStringContainsString('step="any"', $content);
    }

    /**
     * @dataProvider validBonusQuantityProvider
     */
    public function test_decode_cart_accepts_decimal_quantities_for_bonus_items($bonusQty, $expectedFloat): void
    {
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $refMethod = new \ReflectionMethod($controller, 'decodeCart');
        $refMethod->setAccessible(true);

        $request = new OrderRequest();
        $request->merge([
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => 1]]),
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => $bonusQty]]),
        ]);

        $carts = $refMethod->invoke($controller, $request);
        $this->assertCount(2, $carts);
        $bonusItem = $carts[1];
        $this->assertTrue($bonusItem->is_bonus);
        $this->assertEquals($expectedFloat, (float) $bonusItem->product_qty);
    }

    public static function validBonusQuantityProvider(): array
    {
        return [
            '0.5 qty' => [0.5, 0.5],
            '0.25 qty' => [0.25, 0.25],
            '1.5 qty' => [1.5, 1.5],
            '2.75 qty' => [2.75, 2.75],
            'string 0.5' => ['0.5', 0.5],
            'whole number 1' => [1, 1.0],
            'whole number 5' => [5, 5.0],
        ];
    }

    /**
     * @dataProvider invalidBonusQuantityProvider
     */
    public function test_decode_cart_rejects_invalid_quantities_for_bonus_items($invalidQty): void
    {
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $refMethod = new \ReflectionMethod($controller, 'decodeCart');
        $refMethod->setAccessible(true);

        $request = new OrderRequest();
        $request->merge([
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => 1]]),
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => $invalidQty]]),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $refMethod->invoke($controller, $request);
    }

    public static function invalidBonusQuantityProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'empty string' => [''],
            'text' => ['invalid_text'],
            'null' => [null],
        ];
    }

    public function test_decode_cart_still_rejects_decimal_quantities_for_regular_items(): void
    {
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $refMethod = new \ReflectionMethod($controller, 'decodeCart');
        $refMethod->setAccessible(true);

        $request = new OrderRequest();
        $request->merge([
            'dataCarts' => json_encode([['product_id' => 1, 'product_type' => 'product', 'product_qty' => 1.5]]),
            'dataBonusCarts' => json_encode([['product_id' => 2, 'product_type' => 'product', 'product_qty' => 1]]),
        ]);

        try {
            $refMethod->invoke($controller, $request);
            $this->fail('Expected ValidationException was not thrown for decimal regular item');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('dataCarts', $e->errors());
            $this->assertArrayNotHasKey('dataBonusCarts', $e->errors());
        }
    }
}

