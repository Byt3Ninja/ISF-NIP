<?php

namespace database\seeds;

use App\Settings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        $json = File::get(base_path('database/dbFiles/settings.json'));
        $data = json_decode($json);
        foreach ($data as $value){
            Settings::create($value);
        }
    }
}
