<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            // Punjab
            ['name' => 'Lahore'],
            ['name' => 'Faisalabad'],
            ['name' => 'Rawalpindi'],
            ['name' => 'Gujranwala'],
            ['name' => 'Multan'],
            ['name' => 'Sargodha'],
            ['name' => 'Sialkot'],
            ['name' => 'Bahawalpur'],
            ['name' => 'Sheikhupura'],
            ['name' => 'Jhang'],

            // Sindh
            ['name' => 'Karachi'],
            ['name' => 'Hyderabad'],
            ['name' => 'Sukkur'],
            ['name' => 'Larkana'],
            ['name' => 'Nawabshah'],
            ['name' => 'Mirpurkhas'],
            ['name' => 'Jacobabad'],
            ['name' => 'Shikarpur'],

            // KPK
            ['name' => 'Peshawar'],
            ['name' => 'Abbottabad'],
            ['name' => 'Mardan'],
            ['name' => 'Swat'],
            ['name' => 'Kohat'],
            ['name' => 'Bannu'],
            ['name' => 'Charsadda'],
            ['name' => 'Dera Ismail Khan'],

            // Balochistan
            ['name' => 'Quetta'],
            ['name' => 'Gwadar'],
            ['name' => 'Turbat'],
            ['name' => 'Khuzdar'],
            ['name' => 'Sibi'],
            ['name' => 'Hub'],
            ['name' => 'Ziarat'],

            // Islamabad Capital Territory
            ['name' => 'Islamabad'],

            // Azad Jammu & Kashmir (AJK)
            ['name' => 'Muzaffarabad'],
            ['name' => 'Mirpur'],
            ['name' => 'Rawalakot'],

            // Gilgit-Baltistan
            ['name' => 'Gilgit'],
            ['name' => 'Skardu'],
            ['name' => 'Hunza'],
        ];

        DB::table('cities')->insert($cities);
    }
}
