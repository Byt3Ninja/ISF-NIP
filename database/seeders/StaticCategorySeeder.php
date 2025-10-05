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
        ['name' => 'Food & Beverage', 'is_other' => false],
        ['name' => 'Technology', 'is_other' => false],
        ['name' => 'App Website', 'is_other' => false],
        ['name' => 'Housewares Home Design', 'is_other' => false],
        ['name' => 'Health Wellness Nutrition', 'is_other' => false],
        ['name' => 'Fitness', 'is_other' => false],
        ['name' => 'Sports', 'is_other' => false],
        ['name' => 'Beauty', 'is_other' => false],
        ['name' => 'Clothing Fashion', 'is_other' => false],
        ['name' => 'Toys Games', 'is_other' => false],
        ['name' => 'Children', 'is_other' => false],
        ['name' => 'Entertainment Experiential', 'is_other' => false],
        ['name' => 'Pets', 'is_other' => false],
        ['name' => 'Music', 'is_other' => false],
        ['name' => 'Holiday', 'is_other' => false],
        ['name' => 'Other', 'is_other' => true],
    ];

    foreach ($categories as $category) {
        \App\StaticCategory::create($category);
    }
    }
}
