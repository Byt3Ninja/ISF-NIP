<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $toInsert = [
            [
                'name_ar' => 'الفكرة/الإبداع',
                'name_en' => 'Idea/Ideation',
                'description_ar' => 'المرحلة التي يتم فيها توليد الأفكار وبنائها.',
                'description_en' => 'The stage where ideas are generated and developed.',
            ],
            [
                'name_ar' => 'البحث والتطوير',
                'name_en' => 'Research & Development',
                'description_ar' => 'المرحلة التي يتم فيها البحث وتطوير المفاهيم.',
                'description_en' => 'The stage where research and concept development take place.',
            ],
            [
                'name_ar' => 'التحقق',
                'name_en' => 'Validation',
                'description_ar' => 'المرحلة التي يتم فيها التحقق من صحة الفكرة أو المفهوم.',
                'description_en' => 'The stage where the idea or concept is validated.',
            ],
            [
                'name_ar' => 'النموذج الأولي/المنتج الأولي',
                'name_en' => 'Prototype/MVP',
                'description_ar' => 'المرحلة التي يتم فيها إنشاء نموذج أولي أو منتج أولي.',
                'description_en' => 'The stage where a prototype or MVP is created.',
            ],
            [
                'name_ar' => 'الإصدار التجريبي/الإطلاق الأولي',
                'name_en' => 'Beta/Soft Launch',
                'description_ar' => 'المرحلة التي يتم فيها الإطلاق التجريبي للمنتج.',
                'description_en' => 'The stage where the product is soft-launched for testing.',
            ],
            [
                'name_ar' => 'المنتج الجاهز/التشغيل',
                'name_en' => 'Ready Product/Operating',
                'description_ar' => 'المرحلة التي يصبح فيها المنتج جاهزاً ويتم تشغيله.',
                'description_en' => 'The stage where the product is ready and operational.',
            ],
            [
                'name_ar' => 'النمو/التوسع',
                'name_en' => 'Scale/Expansion',
                'description_ar' => 'المرحلة التي يتم فيها توسيع نطاق العمل أو المنتج.',
                'description_en' => 'The stage where the business or product scales and expands.',
            ],
            [
                'name_ar' => 'النضوج',
                'name_en' => 'Maturity',
                'description_ar' => 'المرحلة التي يصل فيها المنتج أو العمل إلى النضوج.',
                'description_en' => 'The stage where the product or business reaches maturity.',
            ],
        ];
        $stored_stages = DB::table('stages')->get();

        $toInsert = collect( $toInsert )->filter( function ( $stage ) use ( $stored_stages ) {
            return ! $stored_stages->contains( 'name_en', $stage['name_en'] );
        })->values()->toArray();

        DB::table('stages')->insert( $toInsert );
    }
}
