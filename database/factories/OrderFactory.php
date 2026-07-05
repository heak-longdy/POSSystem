<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $products = Product::inRandomOrder()->limit(5)->get();
        $service = new \App\Services\OrderService();
        return [
            'invoice_no' => $service->getUniqueInvoiceNumber('KS'),
            'user_id' => 17,
            'user_data' => json_encode(\App\Models\User::find(17)), //Json
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'delivery_fee' => 1,
            'total_discount' => $products->sum('discount'),
            'total_price' => $products->sum('price'),
            'total_product' => $products->count(),
            'total_point_value' => $products->sum('point_value'),
            'status' => 1,
        ];
    }
}
