<?php

namespace Database\Seeders;

use App\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CountryStateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        $json = File::get(base_path('database/dbFiles/countries_states_cities.json'));
        $data = json_decode($json);

        $iso3Codes = ['EGY', 'SAU', 'ARE', 'QAT', 'BHR'];

        $counties = array_filter($data, function ($q) use ($iso3Codes) {
            return in_array($q->iso3, $iso3Codes);
        });
        foreach ($counties as $value) {
            $country =  Country::create(['name_ar' => $value->native, 'name_en' => $value->name, 'iso3' => $value->iso3, 'phone_code' => $value->phone_code]);
            foreach ($value->states as $key) {
                $state = $country->states()->create([
                    'name_ar' => GoogleTranslate::trans($key->name, 'ar', 'en'),
                    'name_en' => $key->name
                ]);
                foreach ($key->cities as $value) {
                    $state->cities()->create([
                        'country_id' => $country->id,
                        'name_ar' => GoogleTranslate::trans($value->name, 'ar', 'en'),
                        'name_en' => $value->name
                    ]);
                }
            }
        }
    }
}
