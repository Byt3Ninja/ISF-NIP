<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GV-10436: educational institutions a founder picks after their Education Status (ISF).
 * Managed by admins; App\EducationEntity maps each status to the types it may pick from.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('education_entities')) {
            return;
        }

        Schema::create('education_entities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index(); // university | institute_directorate | research_center
            $table->string('name_en', 191);
            $table->string('name_ar', 191);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_entities');
    }
};
