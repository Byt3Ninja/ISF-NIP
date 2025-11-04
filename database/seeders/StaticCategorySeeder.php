<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaticCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
        ['name_ar' => 'الأطعمة والمشروبات','name_en' => 'Food & Beverage', 'is_other' => false],
        ['name_ar' => 'التكنولوجيا', 'name_en' => 'Technology', 'is_other' => false],
        ['name_ar' => 'تطبيق / موقع إلكتروني', 'name_en'=> 'App Website', 'is_other' => false],
        ['name_ar' => 'الأدوات المنزلية / تصميم المنزل', 'name_en' => 'Housewares Home Design', 'is_other' => false],
        ['name_ar' => 'الصحة / العافية / التغذية', 'name_en' => 'Health Wellness Nutrition', 'is_other' => false],
        ['name_ar' => 'اللياقة البدنية', 'name_en' => 'Fitness', 'is_other' => false],
        ['name_ar' => 'الرياضة', 'name_en' => 'Sports', 'is_other' => false],
        ['name_ar' => 'الجمال', 'name_en' => 'Beauty', 'is_other' => false],
        ['name_ar' => 'الملابس / الأزياء', 'name_en' => 'Clothing Fashion', 'is_other' => false],
        ['name_ar' => 'الألعاب / الألعاب الإلكترونية', 'name_en' => 'Toys Games', 'is_other' => false],
        ['name_ar' => 'الأطفال', 'name_en' => 'Children', 'is_other' => false],
        ['name_ar' =>  'الترفيه / التجارب', 'name_en' => 'Entertainment Experiential', 'is_other' => false],
        ['name_ar' => 'الحيوانات الأليفة', 'name_en'=> 'Pets', 'is_other' => false],
        ['name_ar' => 'الموسيقى', 'name_en' => 'Music', 'is_other' => false],
        ['name_ar' => 'العطلات', 'name_en' => 'Holiday', 'is_other' => false],
        ['name_ar' => 'أخرى', 'name_en' => 'Other', 'is_other' => true],
    ];

    foreach ($categories as $category) {
        \App\StaticCategory::updateOrCreate(
            ['name_en' => $category['name_en']], 
            $category                          
        );
    }
    }
}
