<?php

namespace Database\Seeders;

use App\Models\MenuService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MenuService::truncate();
        //001
        $id_001 = MenuService::create([
            'name' => json_encode(['en'=>'របាយការណ៏តាមដានសុខភាពរថយន្ត']),
            'image' => '/001.png',
            'level' => 1,
            'user_id' => 2,
            'status' => 1
        ]);
        MenuService::create([
            'name' => json_encode(['en'=>'លទ្ធផលសរុបក្រោយត្រួតពិនិត្យសុខភាពរថយន្ដ']),
            'image' => '/001_1.png',
            'level' => 1,
            'user_id' => 2,
            'status' => 1,
            'parent_id' => $id_001->id,
        ]);
        MenuService::create([
            'name' => json_encode(['en'=>'លទ្ធផលត្រូវបានជួសជុល ក្រោយត្រួតពិនិត្យ']),
            'image' => '/001_2.png',
            'level' => 2,
            'user_id' => 2,
            'status' => 1,
            'parent_id' => $id_001->id,
        ]);
        MenuService::create([
            'name' => json_encode(['en'=>'កាតព្វកិច្ចដែលត្រូវតាមដានសុខភាពរថយន្ដ']),
            'image' => '/001_3.png',
            'level' => 3,
            'user_id' => 2,
            'status' => 1,
            'parent_id' => $id_001->id,
        ]);

        //002
        $id_002 = MenuService::create([
            'name' => json_encode(['en'=>'សេវាកម្មថែទាំសុខភាពរថយន្ត']),
            'image' => '/result.png',
            'level' => 2,
            'user_id' => 2,
            'status' => 1
        ]);

        //003
        $id_003 = MenuService::create([
            'name' => json_encode(['en'=>'ចំណេះដឹងអំពីការថែទាំងសុខភាពរថយន្ដ']),
            'image' => '/data-analytics.png',
            'level' => 3,
            'user_id' => 2,
            'status' => 1
        ]);

        //004
        $id_004 = MenuService::create([
            'name' => json_encode(['en'=>'សេវាកម្មជួយសង្រ្គោះបន្ទាន់']),
            'image' => '/00_4.png',
            'level' => 4,
            'user_id' => 2,
            'status' => 1
        ]);
        //005
        $id_005 = MenuService::create([
            'name' => json_encode(['en'=>'កញ្ចប់ថវិការបំរុង']),
            'image' => '/00_5.png',
            'level' => 5,
            'user_id' => 2,
            'status' => 1
        ]);
    }
}
