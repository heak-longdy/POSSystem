<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\UOM;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = base_path('khmer_energy_drink_products_with_images.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found at: {$jsonPath}");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);

        if (empty($items)) {
            $this->command->error("JSON file is empty or invalid.");
            return;
        }

        Schema::disableForeignKeyConstraints();

        foreach ($items as $item) {
            // Find or Create Category using Khmer name
            $categoryName = $item['category_khmer'] ?? $item['category_english'] ?? 'General';
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['status' => 1]
            );

            // Find or Create UOM using Khmer name
            $uomName = $item['uom_khmer'] ?? $item['uom_english'] ?? 'Unit';
            $uom = UOM::firstOrCreate(
                ['name' => $uomName],
                ['status' => 1]
            );

            // Process image with file_managers table reference
            $imagePath = null;
            if (!empty($item['image_url'])) {
                $imageRelativePath = $item['image_url']; // e.g. /red_bull_can.png
                $fileName = ltrim($imageRelativePath, '/');
                $fullDiskPath = public_path('file_manager' . $imageRelativePath);
                $fileSize = File::exists($fullDiskPath) ? File::size($fullDiskPath) : 0;
                $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'png';

                // Check if file entry exists in file_managers table
                $fileManager = DB::table('file_managers')
                    ->where('path', $imageRelativePath)
                    ->orWhere('name', $fileName)
                    ->first();

                if (!$fileManager) {
                    DB::table('file_managers')->insert([
                        'user_id'     => 1,
                        'folder_id'   => null,
                        'name'        => $fileName,
                        'path'        => $imageRelativePath,
                        'size'        => (string) $fileSize,
                        'extension'   => $extension,
                        'is_hidden'   => 0,
                        'is_image'    => 1,
                        'is_video'    => 0,
                        'is_audio'    => 0,
                        'is_document' => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                $imagePath = $imageRelativePath;
            }

            // Create or Update Product with Khmer name, category, uom, price, and file_managers image reference
            Product::updateOrCreate(
                ['name' => $item['product_name_khmer']],
                [
                    'category_id' => $category->id,
                    'uom_id'      => $uom->id,
                    'price'       => $item['price_usd'] ?? 0,
                    'cost'        => $item['price_usd'] ?? 0,
                    'image'       => $imagePath,
                    'status'      => 1,
                ]
            );
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info("Successfully seeded " . count($items) . " Khmer products with file_managers system references.");
    }
}
