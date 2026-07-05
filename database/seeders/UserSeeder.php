<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::truncate();

        User::create(
            [
                'name' => 'Super Admin',
                'email' => 'super.admin@gmail.com',
                'password' => bcrypt('admin@00@#$01200#'),
                'phone' => '0129999999',
                'role' => 'super_admin',
                'status' => 1,
                'remember_token' => Str::random(10),
            ]
        );

        User::create(
            [
                'name' => 'LongDy Heak',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin@#$01200#'),
                'phone' => '0123456789',
                'role' => 'admin',
                'status' => 1,
                'remember_token' => Str::random(10),
            ]
        );
    }
}
