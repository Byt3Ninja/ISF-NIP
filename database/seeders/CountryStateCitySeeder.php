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

        $countryCodes = [
            'AFG',
            'ALB',
            'DZA',
            'ASM',
            'AND',
            'AGO',
            'AIA',
            'ATA',
            'ATG',
            'ARG',
            'ARM',
            'ABW',
            'AUS',
            'AUT',
            'AZE',
            'BHS',
            'BHR',
            'BGD',
            'BRB',
            'BLR',
            'BEL',
            'BLZ',
            'BEN',
            'BMU',
            'BOL',
            'BIH',
            'BWA',
            'BRA',
            'BRN',
            'BGR',
            'BFA',
            'BDI',
            'CPV',
            'KHM',
            'CMR',
            'CAN',
            'CYM',
            'CAF',
            'TCD',
            'CHL',
            'CHN',
            'COL',
            'COM',
            'COG',
            'COK',
            'CRI',
            'CIV',
            'HRV',
            'CUB',
            'CUW',
            'CYP',
            'CZE',
            'DNK',
            'DJI',
            'DMA',
            'DOM',
            'ECU',
            'EGY',
            'SLV',
            'GNQ',
            'ERI',
            'EST',
            'SWZ',
            'ETH',
            'FJI',
            'FIN',
            'FRA',
            'GAB',
            'GMB',
            'GEO',
            'GHA',
            'GIB',
            'GRC',
            'GRD',
            'GUM',
            'GTM',
            'GIN',
            'GNB',
            'GUY',
            'GRC',
            'HND',
            'HTI',
            'HUN',
            'ISL',
            'IND',
            'IDN',
            'IRN',
            'IRQ',
            'IRL',
            'ISR',
            'ITA',
            'JAM',
            'JPN',
            'JOR',
            'KAZ',
            'KEN',
            'KIR',
            'KOS',
            'KWT',
            'KGZ',
            'LAO',
            'LVA',
            'LBN',
            'LSO',
            'LBR',
            'LBY',
            'LIE',
            'LTU',
            'LUX',
            'MAC',
            'MDG',
            'MWI',
            'MYS',
            'MDV',
            'MLI',
            'MLT',
            'MHL',
            'MTQ',
            'MUS',
            'MRT',
            'MMR',
            'NAM',
            'NRU',
            'NPL',
            'NLD',
            'NCL',
            'NZL',
            'NIC',
            'NER',
            'NGA',
            'MKD',
            'MNP',
            'NOR',
            'OMN',
            'PAK',
            'PLW',
            'PAN',
            'PNG',
            'PRY',
            'PER',
            'PHL',
            'POL',
            'PRI',
            'PSE',
            'PRT',
            'QAT',
            'ROU',
            'RUS',
            'RWA',
            'REU',
            'KNA',
            'LCA',
            'VCT',
            'WSM',
            'SMR',
            'STP',
            'SAU',
            'SEN',
            'SYC',
            'SLE',
            'SGP',
            'SXM',
            'SVK',
            'SVN',
            'SLB',
            'SOM',
            'ZAF',
            'SGS',
            'KOR',
            'ESP',
            'LKA',
            'SDN',
            'SUR',
            'SWE',
            'CHE',
            'SYR',
            'TJK',
            'TGA',
            'THA',
            'TLS',
            'TGO',
            'TON',
            'TTO',
            'TUN',
            'TUR',
            'TKM',
            'TCA',
            'TUV',
            'UGA',
            'UKR',
            'ARE',
            'GBR',
            'USA',
            'URY',
            'UZB',
            'VUT',
            'VEN',
            'VNM',
            'VGB',
            'WLF',
            'WSM',
            'YEM',
            'ZMB',
            'ZWE'

        ];
        $counties = array_values(array_filter($data, function ($q) use ($countryCodes) {
            return in_array($q->iso3, $countryCodes);
        }));

        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='NGA' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='TCD' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='SDN' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='LBY' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='ZMB' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='TGO' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='NER' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='ZWE' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='MLI' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        // $counties[]=array_values(array_filter($data,function ($q){

        //     return $q->iso3=='TUN' /*$q->iso3=='EGY'|| $q->iso3=='SAU'|| $q->iso3=='ARE'|| $q->iso3=='KWT'|| $q->iso3=='QAT'|| $q->iso3=='BHR'*/;
        // }));
        foreach ($counties as $value) {
            $country =  Country::create(['name_ar' => $value->native, 'name_en' => $value->name, 'iso3' => $value->iso3]);
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
