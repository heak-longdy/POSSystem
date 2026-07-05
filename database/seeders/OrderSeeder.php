<?php

namespace Database\Seeders;

use App\Models\Order;
use Database\Factories\OrderFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = new \App\Services\OrderService();
        // Order::truncate();
        // \App\Models\ProductOrder::truncate();
        // Order::factory()
        //     ->count(50)
        //     ->create();
        $products = \App\Models\Product::where('store_id', 5)->get();
        $order = Order::create([
            'invoice_no' => $service->getUniqueInvoiceNumber('KS'),
            'user_id' => 17,
            'user_data' => json_encode(\App\Models\User::find(17)), //Json
            'address' => 'Borey Piphop Thmey, Cambodian Red Cross (Blvd 271) No. 02, Street 02, Toeuk Laak 3, Khan Tuol Kok.',
            'phone' => '+855 81 298 467',
            'delivery_fee' => 1,
            'total_discount' => $products->sum('discount'),
            'total_price' => $products->sum('price'),
            'total_product' => $products->count(),
            'total_point_value' => $products->sum('point_value'),
            'status' => 1,
        ]);

        $products->each(function ($query) use ($order) {
            $order->details()->create([
                'product_id' => $query->id,
                'service_data' => json_encode($query),
                'store_id' => 5,
                'store_data' => json_encode(\App\Models\StoreInfo::find(5)),
                'qty' => 1,
                'price' => $query->price,
                'discount' => $query->discount,
                'total_price' => $query->price - $query->discount,
                'point_value' => $query->point_value,
            ]);
        });
    }
}
