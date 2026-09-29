<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_expertise', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('expertise_id');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('expertise_id')->references('id')->on('expertises')->cascadeOnDelete();
            $table->unique(['company_id', 'expertise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_expertise');
    }
};
