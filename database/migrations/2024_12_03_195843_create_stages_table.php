<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->nullable(); // اسم المرحلة بالعربية
            $table->string('name_en')->nullable(); // اسم المرحلة بالإنجليزية
            $table->text('description_ar')->nullable(); // وصف المرحلة بالعربية
            $table->text('description_en')->nullable(); // وصف المرحلة بالإنجليزية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
