<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Customer::truncate();
        Schema::enableForeignKeyConstraints();

        $customers = [
            [
                'name'        => 'សុខ ដារ៉ា (Sok Dara)',
                'ordering'    => 1,
                'phone'       => '012888999',
                'address'     => 'ផ្ទះលេខ ១៥ ផ្លូវ ២៧១ សង្កាត់បឹងសាឡាង ខណ្ឌទួលគោក ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 120,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'កែវ បុប្ផា (Keo Bopha)',
                'ordering'    => 2,
                'phone'       => '010555666',
                'address'     => 'ផ្ទះលេខ ៤២ ផ្លូវ ៦៣ សង្កាត់បឹងកេងកង១ ខណ្ឌបឹងកេងកង ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 85,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ម៉េង ហេង (Meng Heng)',
                'ordering'    => 3,
                'phone'       => '015777888',
                'address'     => 'ផ្ទះលេខ ៨៨ ផ្លូវ ២០០៤ សង្កាត់កាកាប ខណ្ឌពោធិ៍សែនជ័យ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 240,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ចាន់ ស្រីរ័ត្ន (Chan Sreyroth)',
                'ordering'    => 4,
                'phone'       => '077222333',
                'address'     => 'ភូមិព្រែកហូរ ឃុំព្រែកហូរ ក្រុងតាខ្មៅ ខេត្តកណ្ដាល',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 50,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'លី វិសាល (Ly Visal)',
                'ordering'    => 5,
                'phone'       => '098333444',
                'address'     => 'ផ្ទះលេខ ១២A ផ្លូវ ៥៩៨ សង្កាត់ភ្នំពេញថ្មី ខណ្ឌសែនសុខ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 310,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'រតនា វណ្ណា (Rathana Vanna)',
                'ordering'    => 6,
                'phone'       => '089444555',
                'address'     => 'ផ្ទះលេខ ៧៧ ផ្លូវព្រះនរោត្តម សង្កាត់ជ័យជំនះ ខណ្ឌដូនពេញ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 15,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ជា ប៊ុនធឿន (Chea Bunthoeun)',
                'ordering'    => 7,
                'phone'       => '0975551234',
                'address'     => 'ភូមិវត្តបូព៌ សង្កាត់សាលាកំរើក ក្រុងសៀមរាប ខេត្តសៀមរាប',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 450,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ទេព ពិសី (Tep Pisey)',
                'ordering'    => 8,
                'phone'       => '012999111',
                'address'     => 'ផ្ទះលេខ ២៤ ផ្លូវ ៣៦០ សង្កាត់ទួលស្វាយព្រៃ១ ខណ្ឌបឹងកេងកង ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 60,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'មាស សម្បត្តិ (Meas Sambath)',
                'ordering'    => 9,
                'phone'       => '011666777',
                'address'     => 'ផ្ទះលេខ ៩៩ ផ្លូវជាតិលេខ១ សង្កាត់ច្បារអំពៅ១ ខណ្ឌច្បារអំពៅ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 520,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ហេង ស្រីពៅ (Heng Sreypov)',
                'ordering'    => 10,
                'phone'       => '086777999',
                'address'     => 'ភូមិអូរអំបិល សង្កាត់អូរអំបិល ក្រុងសិរីសោភ័ណ ខេត្តបន្ទាយមានជ័យ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 95,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'សំណាង ចិន្តា (Somnang Chenda)',
                'ordering'    => 11,
                'phone'       => '093444888',
                'address'     => 'ផ្ទះលេខ ៥ ផ្លូវ ១១៨ សង្កាត់ផ្សារចាស់ ខណ្ឌដូនពេញ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 175,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ខេមរា រិទ្ធ (Khemara Rith)',
                'ordering'    => 12,
                'phone'       => '070333222',
                'address'     => 'ភូមិព្រែកព្រះស្តេច ឃុំព្រែកព្រះស្តេច ក្រុងបាត់ដំបង ខេត្តបាត់ដំបង',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 0,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'សេង ដាវីដ (Seng David)',
                'ordering'    => 13,
                'phone'       => '017111222',
                'address'     => 'ផ្ទះលេខ ៣១ ផ្លូវ ៥១ សង្កាត់ចតុមុខ ខណ្ឌដូនពេញ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 380,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'អ៊ុក សុវណ្ណារិទ្ធ (Ouk Sovannarith)',
                'ordering'    => 14,
                'phone'       => '092888333',
                'address'     => 'ផ្ទះលេខ ៦៣ ផ្លូវ ២១៧ សង្កាត់អូរឫស្សី២ ខណ្ឌ៧មករា ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 130,
                'user'        => 1,
                'status'      => 1,
            ],
            [
                'name'        => 'ឈន បុប្ផាមាស (Chhorn Bophamas)',
                'ordering'    => 15,
                'phone'       => '0969994444',
                'address'     => 'ផ្ទះលេខ ១០៨ ផ្លូវ ២០០២ សង្កាត់ទឹកថ្លា ខណ្ឌសែនសុខ ភ្នំពេញ',
                'profile'     => null,
                'password'    => bcrypt('12345678'),
                'total_point' => 205,
                'user'        => 1,
                'status'      => 1,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
