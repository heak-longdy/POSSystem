<?php

namespace Database\Seeders;

use App\Models\StockType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StockType::truncate();
        StockType::create([
            'key' => 100,
            'name' => 'Sell To Customer',
            'ordering' => 1,
            'user_id' => 1,
            'status' => 1,
        ]);
        StockType::create([
            'key' => 200,
            'name' => 'Shop Use',
            'ordering' => 2,
            'user_id' => 1,
            'status' => 1,
        ]);
        StockType::create([
            'key' => 300,
            'name' => 'Sell To Gift',
            'ordering' => 3,
            'user_id' => 1,
            'status' => 1,
        ]);
    }
}
