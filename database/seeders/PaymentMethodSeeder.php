<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::truncate();
        $item = [
            [
                'name' => [
                    'en' => "Kimly Heng",
                    'km' => "គឹម លីហៀង",
                    'zh' => "金利恆"
                ],
                'account_number' => '123789890',
                'image' => '/aba.jpg',
                'status' => 1,
                'garage_id' => 3,
                'user_id' => 1
            ],
            [
                'name' => [
                    'en' => "Sok Sokun",
                    'km' => "សុគន្ធ",
                    'zh' => "速速坤"
                ],
                'account_number' => '012334455',
                'image' => '/wing.png',
                'status' => 1,
                'garage_id' => 3,
                'user_id' => 1
            ],
            [
                'name' => [
                    'en' => "Cash on hand",
                    'km' => "បង់ប្រាក់តាមរយះការទៅផ្ទាល់",
                    'zh' => "親自付款"
                ],
                'account_number' => 'cash_on_hand',
                'image' => '/cash_on_delivery.png',
                'status' => 1,
                'garage_id' => 3,
                'user_id' => 1
            ]
        ];
        foreach($item as $val){
            $val['name'] = json_encode($val['name']);
            PaymentMethod::create($val);
        }
    }
}
