<?php

namespace Database\Seeders;

use App\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(base_path('database/dbFiles/sectors.json'));
        $data = json_decode($json,true);

        foreach ($data as $value){
            Sector::create($value);
        }
    }
}
