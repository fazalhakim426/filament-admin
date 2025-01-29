<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
class LocationSeeder extends Seeder
{
    public function run()
    { 
        $pakistan = Country::create([
            'name' => 'Pakistan',
            'code' => 'PK',
        ]);
 
        $provinces = [
            'Punjab' => ['Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Sialkot'],
            'Sindh' => ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana'],
            'Khyber Pakhtunkhwa' => ['Peshawar', 'Abbottabad', 'Mardan', 'Swat'],
            'Balochistan' => ['Quetta', 'Gwadar', 'Turbat', 'Khuzdar'],
            'Gilgit-Baltistan' => ['Gilgit', 'Skardu', 'Hunza'],
            'Azad Jammu and Kashmir' => ['Muzaffarabad', 'Mirpur', 'Rawalakot'],
        ];

        foreach ($provinces as $provinceName => $cities) {
            $state = State::create([
                'country_id' => $pakistan->id,
                'name' => $provinceName,
            ]);

            foreach ($cities as $cityName) {
                City::create([
                    'state_id' => $state->id,
                    'name' => $cityName,
                ]);
            }
        }
    }
}
