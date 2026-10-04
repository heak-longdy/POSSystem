<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Supplier::truncate();
        Schema::enableForeignKeyConstraints();

        $suppliers = [
            [
                'name'     => 'ខ្មែរ ប៊ែវើរីជីស (Khmer Beverages)',
                'ordering' => 1,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'អ៊ែតវូដ (Attwood Import Export)',
                'ordering' => 2,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'ភេសជ្ជៈកម្ពុជា (Coca-Cola CBC)',
                'ordering' => 3,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'គោជល់ កម្ពុជា (Red Bull Cambodia)',
                'ordering' => 4,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'ឌីខេអេសអេច (DKSH Cambodia)',
                'ordering' => 5,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'បាកខូស ត្រេឌីង (Bacchus Trading)',
                'ordering' => 6,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'នូត្រា វត្ថុធាតុដើម (Nutra Ingredients)',
                'ordering' => 7,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'ក្រោន កំប៉ុង (Crown Packaging)',
                'ordering' => 8,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'អេភិច វេចខ្ចប់ (Apex Packaging)',
                'ordering' => 9,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
            [
                'name'     => 'ហ្គូដហ៊ីល (Goodhill Enterprise)',
                'ordering' => 10,
                'image'    => null,
                'user_id'  => 1,
                'status'   => 1,
            ],
        ];

        foreach ($suppliers as $item) {
            Supplier::create($item);
        }
    }
}
