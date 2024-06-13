<?php

namespace Database\Seeders;

use App\Expertise;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ExpertiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(base_path('database/dbFiles/expertises.json'));
        $data = json_decode($json,true);

        foreach ($data as $value){
            Expertise::create($value);
        }
    }
}
