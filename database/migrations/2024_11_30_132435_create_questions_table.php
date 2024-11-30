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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->text('text_ar')->nullable()->comment('السؤال عربى');
            $table->text('text_en')->nullable()->comment('السؤال انجليزي');
            $table->text('info_ar')->nullable()->comment('معلومات عن السؤال باللغة العربية ');
            $table->text('info_en')->nullable()->comment('معلومات عن السؤال باللغة الانجليزية ');
            $table->timestamps();

            // علاقات الربط
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('sub_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
