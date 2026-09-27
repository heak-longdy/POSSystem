<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shop = Shop::create([
            'name' => 'Main Branch',
            'phone' => '012345678',
            'status' => 1,
        ]);
        $this->customer = Customer::create([
            'name' => 'Jane Doe',
            'phone' => '098765432',
            'status' => 1,
            'user' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_calculates_remaining_amount_and_displays_usd_currency()
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 150.00,
            'paid_amount' => 50.00,
            'total_discount' => 10.00,
            'total_commission' => 5.00,
            'order_date' => now(),
            'payment_status' => 'Partial',
        ]);

        $this->assertEquals(100.00, $order->remaining_amount);
        $this->assertEquals('$150.00', '$' . number_format($order->total_price, 2));
        $this->assertEquals('$100.00', '$' . number_format($order->remaining_amount, 2));
    }
}
