<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\ShopProductRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductRequestValidationTest extends TestCase
{
    private function validateProductData(array $data): \Illuminate\Validation\Validator
    {
        $request = new ProductRequest();
        $request->merge($data);

        // Call protected prepareForValidation via reflection
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $rules = [
            'category_id' => 'required|integer',
            'uom_id' => 'required|integer',
            'name' => 'required|max:50',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|max:1',
        ];

        return Validator::make($request->all(), $rules, $request->messages());
    }

    public function test_product_request_normalizes_comma_decimal_and_thousands(): void
    {
        $data = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Product 1',
            'cost' => '5,50',
            'price' => '10,50',
            'status' => 1,
        ];

        $validator = $this->validateProductData($data);
        $this->assertFalse($validator->fails(), 'Validation should pass with comma decimals');
        $this->assertEquals(10.50, $validator->getData()['price']);
        $this->assertEquals(5.50, $validator->getData()['cost']);
    }

    public function test_product_request_normalizes_both_separators(): void
    {
        $dataUs = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Product US',
            'cost' => '1,200.50',
            'price' => '1,500.50',
            'status' => 1,
        ];
        $validatorUs = $this->validateProductData($dataUs);
        $this->assertFalse($validatorUs->fails());
        $this->assertEquals(1500.50, $validatorUs->getData()['price']);
        $this->assertEquals(1200.50, $validatorUs->getData()['cost']);

        $dataEu = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Product EU',
            'cost' => '1.200,50',
            'price' => '1.500,50',
            'status' => 1,
        ];
        $validatorEu = $this->validateProductData($dataEu);
        $this->assertFalse($validatorEu->fails());
        $this->assertEquals(1500.50, $validatorEu->getData()['price']);
        $this->assertEquals(1200.50, $validatorEu->getData()['cost']);
    }

    public function test_product_request_normalizes_khmer_numerals(): void
    {
        $data = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Product Khmer',
            'cost' => '៥,២៥',
            'price' => '១០,៥០',
            'status' => 1,
        ];

        $validator = $this->validateProductData($data);
        $this->assertFalse($validator->fails());
        $this->assertEquals(10.50, $validator->getData()['price']);
        $this->assertEquals(5.25, $validator->getData()['cost']);
    }

    public function test_product_request_rejects_invalid_price_and_cost(): void
    {
        $dataInvalid = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Invalid',
            'cost' => 'invalid_cost',
            'price' => '10.50.20',
            'status' => 1,
        ];

        $validator = $this->validateProductData($dataInvalid);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('price'));
        $this->assertTrue($validator->errors()->has('cost'));
    }

    public function test_product_request_rejects_negative_price(): void
    {
        $dataNegative = [
            'category_id' => 1,
            'uom_id' => 1,
            'name' => 'Test Negative',
            'cost' => '10',
            'price' => '-10,50',
            'status' => 1,
        ];

        $validator = $this->validateProductData($dataNegative);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('price'));
    }

    private function validateShopProductData(array $data): \Illuminate\Validation\Validator
    {
        $request = new ShopProductRequest();
        $request->merge($data);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $rules = [
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.status' => 'required|in:1,2',
        ];

        return Validator::make($request->all(), $rules, $request->messages());
    }

    public function test_shop_product_request_normalizes_comma_and_khmer_prices(): void
    {
        $data = [
            'products' => [
                [
                    'product_id' => 1,
                    'product_code' => 'P-001',
                    'price' => '10,50',
                    'status' => 1,
                ],
                [
                    'product_id' => 2,
                    'product_code' => 'P-002',
                    'price' => '1.500,50',
                    'status' => 1,
                ],
                [
                    'product_id' => 3,
                    'product_code' => 'P-003',
                    'price' => '១,៥០០.៥០',
                    'status' => 1,
                ],
            ],
        ];

        $validator = $this->validateShopProductData($data);
        $this->assertFalse($validator->fails(), 'Shop product prices with comma and Khmer digits should be valid');

        $validatedData = $validator->getData()['products'];
        $this->assertEquals('P-001', $validatedData[0]['product_code']);
        $this->assertEquals(10.50, $validatedData[0]['price']);
        $this->assertEquals('P-002', $validatedData[1]['product_code']);
        $this->assertEquals(1500.50, $validatedData[1]['price']);
        $this->assertEquals('P-003', $validatedData[2]['product_code']);
        $this->assertEquals(1500.50, $validatedData[2]['price']);
    }

    public function test_shop_product_request_rejects_invalid_and_negative_prices(): void
    {
        $dataInvalid = [
            'products' => [
                [
                    'product_id' => 1,
                    'price' => '10.50.20',
                    'status' => 1,
                ],
                [
                    'product_id' => 2,
                    'price' => '-10,50',
                    'status' => 1,
                ],
            ],
        ];

        $validator = $this->validateShopProductData($dataInvalid);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('products.0.price'));
        $this->assertTrue($validator->errors()->has('products.1.price'));
    }

    public function test_available_shop_products_route_is_registered(): void
    {
        $url = route('admin-select-available-shop-products');
        $this->assertStringContainsString('/admin/select/available-shop-products', $url);
    }

    public function test_select_controller_has_available_shop_products_method(): void
    {
        $this->assertTrue(
            method_exists(\App\Http\Controllers\Admin\SelectController::class, 'selectAvailableShopProducts'),
            'SelectController must implement selectAvailableShopProducts'
        );
    }

    public function test_shop_product_request_allows_updating_product_id(): void
    {
        $request = new ShopProductRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('products.*.id', $rules);
        $this->assertArrayHasKey('products.*.product_id', $rules);
        $this->assertStringContainsString('required', $rules['products.*.product_id']);
    }
}
