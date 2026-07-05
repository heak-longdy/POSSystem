<?php

namespace Database\Seeders;

use App\Models\MemberLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MemberLocation::truncate();
        $item = [
            [
                'name' =>  "Kimly Heng",
                'phone_number' => '0123344555',
                'address' => 'Kandal,phnom penh',
                'map' => [
                    'latitude' => '900808',
                    'longitude' => '7080808'
                ],
                'status' => 1,
                'member_id' => 3
            ],
            [
                'name' =>  "Hong Ly",
                'phone_number' => '0123344555',
                'address' => 'Kandal,phnom penh',
                'map' => [
                    'latitude' => '900808',
                    'longitude' => '7080808'
                ],
                'status' => 1,
                'member_id' => 3
            ],
        ];
        foreach($item as $val){
            $val['map'] = json_encode($val['map']);
            MemberLocation::create($val);
        }
    }
}
