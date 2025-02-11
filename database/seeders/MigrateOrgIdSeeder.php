<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateOrgIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // جلب الأسئلة التي تحتوي على org_id
         $questions = DB::table('questions_bank')
         ->whereNotNull('organization_id')
         ->select('id', 'organization_id')
         ->get();

     if ($questions->isNotEmpty()) {
         // تحضير البيانات للإدراج في جدول organization_question_bank
         $insertData = $questions->map(function ($question) {
             return [
                 'organization_id' => $question->organization_id,
                 'question_id' => $question->id,
                 'created_at' => now(),
                 'updated_at' => now(),
             ];
         })->toArray();

         // إدخال البيانات في جدول organization_question_bank
         DB::table('organization_question_bank')->insert($insertData);

         // تحديث org_id إلى null في جدول question_bank بعد النقل
         DB::table('questions_bank')
             ->whereIn('id', $questions->pluck('id'))
             ->update(['organization_id' => null]);

         $this->command->info('تم نقل البيانات بنجاح وتحديث org_id إلى null.');
     } else {
         $this->command->info('لا توجد بيانات لنقلها.');
     }
 }
    
}
