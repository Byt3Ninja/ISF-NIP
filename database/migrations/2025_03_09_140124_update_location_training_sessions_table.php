<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // إعادة تسمية العمود باستخدام أمر SQL مباشر
        DB::statement("ALTER TABLE training_sessions CHANGE COLUMN location training_Address VARCHAR(255)");

        // إضافة العمود الجديد بعد العمود المُعاد تسميته
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->text('training_location')->nullable()->comment('training location Google Map')->after('training_Address');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // حذف العمود الجديد
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('training_location');
        });

        // إعادة تسمية العمود إلى اسمه القديم باستخدام SQL مباشر
        DB::statement("ALTER TABLE training_sessions CHANGE COLUMN training_Address location VARCHAR(255)");
    }
};
