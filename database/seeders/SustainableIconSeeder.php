<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SustainableIconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $sustainableIcons = [
            ['id' => 1, 'title' => 'no_poverty', 'icon_ar' => '1.png', 'icon_en' => '1.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'title' => 'zero_hunger', 'icon_ar' => '2.png', 'icon_en' => '2.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'title' => 'good_health', 'icon_ar' => '3.png', 'icon_en' => '3.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'title' => 'quality_education', 'icon_ar' => '4.png', 'icon_en' => '4.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'title' => 'gender_equality', 'icon_ar' => '5.png', 'icon_en' => '5.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'title' => 'clean_water', 'icon_ar' => '6.png', 'icon_en' => '6.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'title' => 'clean_energy', 'icon_ar' => '7.png', 'icon_en' => '7.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'title' => 'decent_work', 'icon_ar' => '8.png', 'icon_en' => '8.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'title' => 'industry_innovation', 'icon_ar' => '9.png', 'icon_en' => '9.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'title' => 'reduced_inequalities', 'icon_ar' => '10.png', 'icon_en' => '10.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'title' => 'sustainable_cities', 'icon_ar' => '11.png', 'icon_en' => '11.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'title' => 'responsible_consumption', 'icon_ar' => '12.png', 'icon_en' => '12.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'title' => 'climate_action', 'icon_ar' => '13.png', 'icon_en' => '13.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'title' => 'life_below_water', 'icon_ar' => '14.png', 'icon_en' => '14.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'title' => 'life_on_land', 'icon_ar' => '15.png', 'icon_en' => '15.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'title' => 'peace_justice', 'icon_ar' => '16.png', 'icon_en' => '16.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'title' => 'partnership_goals', 'icon_ar' => '17.png', 'icon_en' => '17.png', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($sustainableIcons as $icon) {
            DB::table('sustainable_icon')->updateOrInsert(
                ['id' => $icon['id']], // شرط الوجود
                array_merge($icon, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
