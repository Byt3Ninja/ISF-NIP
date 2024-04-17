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
        Schema::create('service_list_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_list_id')->constrained()->cascadeOnDelete();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('desc_ar')->nullable();
            $table->string('desc_en')->nullable();
            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_list_features');
    }
};
