<?php

namespace Database\Seeders;

use App\Field;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(base_path('database/dbFiles/fields.json'));
        $data = json_decode($json,true);
        
        foreach ($data as $value){
            // Field::create($value);
            Field::firstOrCreate(
                ['field_type' => $value['field_type']],
            );
        }
    }
}
