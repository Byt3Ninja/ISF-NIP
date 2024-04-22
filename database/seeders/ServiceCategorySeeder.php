<?php

namespace Database\Seeders;

use App\Service;
use App\ServiceCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonCategory = File::get(base_path('database/dbFiles/service_categories.json'));
        $dataCategory = json_decode($jsonCategory,true);
        $jsonService = File::get(base_path('database/dbFiles/services.json'));
        $dataService = json_decode($jsonService,true);

        foreach ($dataCategory as $value){
            ServiceCategory::create($value);
        }
        foreach ($dataService as $value){
            Service::create($value);
        }
    }
}
