<?php

namespace Database\Seeders;
use App\Models\JobList;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Define batch size
        $batchSize = 1000; // Inserting 1000 records per batch

        // Total batches to create 1 billion records
        $totalBatches = 10000; // 1,000,000 batches of 1000 records = 1 billion total records

        // Loop to insert in batches
        foreach (range(1, $totalBatches) as $batch) {
            $data = [];

            // Generate data for this batch
            foreach (range(1, $batchSize) as $index) {
                $data[] = [
                    'number_of_day' => $faker->numberBetween(1, 365),
                    'post_date' => $faker->dateTimeBetween('-1 year', 'now'),
                    'close_date' => $faker->dateTimeBetween('now', '+1 year'),
                    'user' => $faker->userName,
                    'placement_type_id' => $faker->numberBetween(1, 10),
                    'position_id' => $faker->numberBetween(1, 50),
                    'sector_id' => $faker->numberBetween(1, 20),
                    'company_id' => $faker->numberBetween(1, 100),
                    'country_id' => $faker->numberBetween(1, 200),
                    'job_category_id' => $faker->numberBetween(1, 10),
                    'location_id' => $faker->numberBetween(1, 500),
                    'title' => $faker->jobTitle,
                    'term' => $faker->randomElement(['Full-time', 'Part-time', 'Contract']),
                    'salary' => $faker->numberBetween(30000, 100000),
                    'year_experience' => $faker->numberBetween(0, 20),
                    'number_of_hire' => $faker->numberBetween(1, 10),
                    'status' => $faker->randomElement(['open', 'closed', 'pending']),
                    'image' => $faker->imageUrl(640, 480, 'business', true),
                    'sex' => $faker->randomElement(['Male', 'Female']),
                    'is_premium' => $faker->boolean,
                    'job_level' => $faker->randomElement(['Entry', 'Mid', 'Senior']),
                    'job_des' => $faker->text(200),
                    'job_requirement' => $faker->text(200),
                    'job_res' => $faker->text(200),
                    'job_type' => $faker->randomElement(['Permanent', 'Temporary', 'Internship']),
                    'hr_name' => $faker->name,
                    'hr_phone_number' => $faker->phoneNumber,
                    'hr_email' => $faker->email,
                    'salary_from' => $faker->numberBetween(30000, 50000),
                    'salary_to' => $faker->numberBetween(50000, 100000),
                    'deleted_at' => null, // If you want to use soft deletes
                    'is_negotiate' => $faker->boolean,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert the batch
            JobList::insert($data);

            // Display progress every 1000 batches
            if ($batch % 1000 === 0) {
                echo "Inserted $batch batches (" . ($batch * $batchSize) . " records)\n";
            }
        }
    }
}
