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
        $json = File::get(base_path('database/dbFiles/countries+states+cities.json'));
        $data = json_decode($json);

        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='NGA' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='TCD' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='SDN' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='LBY' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='ZMB' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='TGO' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='NER' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='ZWE' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='MLI' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
        $counties[]=array_values(array_filter($data,function ($q){

            return $q->iso3=='TUN' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        }));
    foreach ($counties as $value){
        $country =  Country::create(['name_ar'=>$value[0]->native,'name_en'=>$value[0]->name,'iso3'=>$value[0]->iso3]);
        foreach($value[0]->states as $key){
            $state = $country->states()->create([
                'name_ar'=>GoogleTranslate::trans($key->name, 'ar', 'en'),
                'name_en'=>$key->name
            ]);
            foreach ($key->cities as $value){
                $state->cities()->create([
                    'country_id'=>$country->id,
                    'name_ar'=>GoogleTranslate::trans($value->name, 'ar', 'en'),
                    'name_en'=>$value->name
                ]);
            }
        }
    }

    }
}
