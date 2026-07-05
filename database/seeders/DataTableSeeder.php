<?php

namespace Database\Seeders;

use App\Models\DataTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use Faker\Factory as Faker;

class DataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    // public function run()
    // {
    //     $faker = Faker::create();
    //     foreach (range(1, 10000000) as $index) {
    //         DataTable::create([
    //             'name' => $faker->name,
    //             'position' => $faker->jobTitle,
    //             'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
    //             'hired_on' => $faker->date('Y-m-d', 'now'),
    //         ]);
    //     }
    // }
    // public function run()
    // {
    //     $faker = Faker::create();

    //     // Specify batch size
    //     $batchSize = 10000;

    //     // Insert in batches of 1000
    //     foreach (range(1, 10000) as $batch) {  // 10000 batches of 1000 records = 10,000,000 total records
    //         $data = [];
    //         foreach (range(1, $batchSize) as $index) {
    //             $data[] = [
    //                 'name' => $faker->name,
    //                 'position' => $faker->jobTitle,
    //                 'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
    //                 'hired_on' => $faker->date('Y-m-d', 'now'),
    //             ];
    //         }

    //         // Perform batch insert
    //         DataTable::insert($data);

    //         // Optionally, display progress
    //         echo "Inserted batch $batch \n";
    //     }
    // }
    public function run()
    {
        $faker = Faker::create();

        // Define batch size
        $batchSize = 1000; // Inserting 1000 records per batch

        // Total batches to create 1 billion records
        $totalBatches = 1000000; // 1,000,000 batches of 1000 records = 1 billion total records

        // Loop to insert in batches
        foreach (range(1, $totalBatches) as $batch) {
            $data = [];

            // Generate data for this batch
            foreach (range(1, $batchSize) as $index) {
                $data[] = [
                    'name' => $faker->name,
                    'position' => $faker->jobTitle,
                    'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
                    'hired_on' => $faker->date('Y-m-d', 'now'),
                ];
            }

            // Insert the batch
            DataTable::insert($data);

            // Display progress every 1000 batches
            if ($batch % 1000 === 0) {
                echo "Inserted $batch batches (" . ($batch * $batchSize) . " records)\n";
            }
        }
    }
}
