<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Staff::truncate();
        Schema::enableForeignKeyConstraints();

        // Realistic Khmer Positions
        $positionsList = [
            'អ្នកគ្រប់គ្រងទូទៅ',
            'បេឡាធិការ',
            'គណនេយ្យករ',
            'អ្នកកាត់សក់ឆ្នើម',
            'អ្នកគ្រប់គ្រងឃ្លាំង',
            'បុគ្គលិកសេវាកម្ម',
            'អ្នកត្រួតពិនិត្យ',
            'បុគ្គលិកទទួលភ្ញៀវ',
            'អ្នកកាត់សក់',
            'អ្នកថែទាំអនាម័យ',
        ];

        $positionIds = [];
        foreach ($positionsList as $index => $posTitle) {
            $pos = Position::firstOrCreate(
                ['title' => $posTitle],
                [
                    'status' => 1,
                    'order'  => $index + 1,
                    'user'   => 1,
                ]
            );
            $positionIds[] = $pos->id;
        }

        // 15 Pure Khmer Staff Records
        $staffMembers = [
            [
                'name'         => 'សុខ ចាន់ថា',
                'position_id'  => $positionIds[0] ?? null,
                'phone_number' => '012345678',
                'email'        => 'sok.chantha@pos.com',
                'address'      => 'ផ្ទះលេខ ១២ ផ្លូវ ២៧១ សង្កាត់បឹងសាឡាង ខណ្ឌទួលគោក ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'គឹម សុវណ្ណ',
                'position_id'  => $positionIds[1] ?? null,
                'phone_number' => '012876543',
                'email'        => 'kim.sovann@pos.com',
                'address'      => 'ផ្ទះលេខ ៤៥ ផ្លូវ ៦៣ សង្កាត់បឹងកេងកង១ ខណ្ឌបឹងកេងកង ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ចាន់ ស្រីណេត',
                'position_id'  => $positionIds[2] ?? null,
                'phone_number' => '015234567',
                'email'        => 'chan.sreynet@pos.com',
                'address'      => 'ភូមិព្រែកហូរ ឃុំព្រែកហូរ ស្រុកតាខ្មៅ ខេត្តកណ្ដាល',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ហេង វិចិត្រ',
                'position_id'  => $positionIds[3] ?? null,
                'phone_number' => '0978881234',
                'email'        => 'heng.vicheat@pos.com',
                'address'      => 'ផ្ទះលេខ ៨៨ ផ្លូវ ២០០៤ សង្កាត់កាកាប ខណ្ឌពោធិ៍សែនជ័យ ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'លី រតនៈ',
                'position_id'  => $positionIds[4] ?? null,
                'phone_number' => '077999111',
                'email'        => 'ly.rothanak@pos.com',
                'address'      => 'ភូមិវត្តបូព៌ សង្កាត់សាលាកំរើក ក្រុងសៀមរាប ខេត្តសៀមរាប',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'អ៊ុំ ស្រីពៅ',
                'position_id'  => $positionIds[5] ?? null,
                'phone_number' => '0885554321',
                'email'        => 'oum.sreypov@pos.com',
                'address'      => 'ភូមិរំជួល ឃុំរំជួល ស្រុកបាត់ដំបង ខេត្តបាត់ដំបង',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ជៀម វិបុល',
                'position_id'  => $positionIds[6] ?? null,
                'phone_number' => '016444555',
                'email'        => 'chiem.vibol@pos.com',
                'address'      => 'ផ្ទះលេខ ១៥ ផ្លូវ ៥៩៨ សង្កាត់ភ្នំពេញថ្មី ខណ្ឌសែនសុខ ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ប្រាក់ ពិសិដ្ឋ',
                'position_id'  => $positionIds[7] ?? null,
                'phone_number' => '010333222',
                'email'        => 'prak.piset@pos.com',
                'address'      => 'ភូមិទី១ សង្កាត់កំពង់ចាម ក្រុងកំពង់ចាម ខេត្តកំពង់ចាម',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ឡុង ស្រីលក្ខណ៍',
                'position_id'  => $positionIds[7] ?? null,
                'phone_number' => '096777888',
                'email'        => 'long.sreyleak@pos.com',
                'address'      => 'ផ្ទះលេខ ១០២ ផ្លូវ ៣៦០ សង្កាត់ផ្សារដើមថ្កូវ ខណ្ឌចំការមន ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'កែវ សុភា',
                'position_id'  => $positionIds[6] ?? null,
                'phone_number' => '011222333',
                'email'        => 'keo.sophea@pos.com',
                'address'      => 'ភូមិកំពង់បាយ ឃុំកំពង់បាយ ស្រុកកំពង់បាយ ខេត្តកំពត',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ម៉ម វណ្ណា',
                'position_id'  => $positionIds[8] ?? null,
                'phone_number' => '089111222',
                'email'        => 'mom.vanna@pos.com',
                'address'      => 'ផ្ទះលេខ ៣៣ ផ្លូវ ១១៨៦ សង្កាត់ភ្នំពេញថ្មី ខណ្ឌសែនសុខ ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ទៀង សុខជា',
                'position_id'  => $positionIds[6] ?? null,
                'phone_number' => '098444333',
                'email'        => 'tieng.sokchea@pos.com',
                'address'      => 'ភូមិ២ សង្កាត់២ ក្រុងព្រះសីហនុ ខេត្តព្រះសីហនុ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ជា ស្រីមុំ',
                'position_id'  => $positionIds[1] ?? null,
                'phone_number' => '070555666',
                'email'        => 'chea.sreymom@pos.com',
                'address'      => 'ផ្ទះលេខ ៥៦ ផ្លូវ ១១០ សង្កាត់វត្តភ្នំ ខណ្ឌដូនពេញ ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'ណុប សំបូរ',
                'position_id'  => $positionIds[8] ?? null,
                'phone_number' => '093666777',
                'email'        => 'nop.sambath@pos.com',
                'address'      => 'ភូមិរាមអណ្ដែត ឃុំរាមអណ្ដែត ស្រុកគិរីវង់ ខេត្តតាកែវ',
                'status'       => 1,
                'user'         => 1,
            ],
            [
                'name'         => 'អ៊ុក ចាន់ដារ៉ា',
                'position_id'  => $positionIds[9] ?? null,
                'phone_number' => '017888999',
                'email'        => 'ouk.chandara@pos.com',
                'address'      => 'ផ្ទះលេខ ៧៧ ផ្លូវ ២៧១ សង្កាត់ទឹកថ្លា ខណ្ឌសែនសុខ ភ្នំពេញ',
                'status'       => 1,
                'user'         => 1,
            ],
        ];

        foreach ($staffMembers as $staffData) {
            Staff::create($staffData);
        }

        $this->command->info("Successfully seeded 15 Khmer staff records into 'staffs' table.");
    }
}
