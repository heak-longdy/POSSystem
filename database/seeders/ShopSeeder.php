<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Shop::truncate();
        Schema::enableForeignKeyConstraints();

        Shop::create([
            'name'         => 'Warehouse',
            'nick_name'    => 'Warehouse',
            'phone'        => '012000001',
            'address'      => 'Phnom Penh, Cambodia',
            'image'        => null,
            'password'     => bcrypt('12345678'),
            'total_wallet' => 0,
            'total_point'  => 0,
            'status'       => 1,
        ]);
    }
}
