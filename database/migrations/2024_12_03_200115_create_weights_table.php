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
        Schema::create('weights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stage_id'); // عمود stage_id
            $table->unsignedBigInteger('category_id'); // عمود category_id
            $table->unsignedBigInteger('subcategory_id'); // عمود subcategory_id
            $table->unsignedBigInteger('question_id'); // عمود question_id
            $table->float('weight')->default(0); // عمود الوزن weight
            $table->timestamps(); // created_at و updated_at

            // علاقات محتملة مع الجداول الأخرى (اختياري)
            $table->foreign('stage_id')->references('id')->on('stages')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions_bank')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weights');
    }
};
