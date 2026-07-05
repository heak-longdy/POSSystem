<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supplier::truncate();
        Supplier::create([
            'name' => 'Supplier#001',
            'ordering' => 1,
            'user_id' => 1,
            'status' => 1,
        ]);
        Supplier::create([
            'name' => 'Supplier#002',
            'ordering' => 2,
            'user_id' => 1,
            'status' => 1,
        ]);
        Supplier::create([
            'name' => 'Supplier#003',
            'ordering' => 3,
            'user_id' => 1,
            'status' => 1,
        ]);
    }
}
