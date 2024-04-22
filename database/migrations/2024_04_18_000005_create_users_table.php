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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name_en',250);
            $table->string('last_name_en',250);
            $table->string('first_name_ar', 250)->nullable();
            $table->string('last_name_ar')->nullable();
            $table->string('gender', 7)->default('Male');
            $table->string('phone', 100)->nullable();
            $table->string('email')->unique();
            $table->string('title_ar', 250)->nullable();
            $table->string('title_en', 250)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->date('dob')->nullable();
            $table->string('academic_stat')->nullable();
            $table->string('university')->nullable();
            $table->string('faculty')->nullable();
            $table->string('employer')->nullable();
            $table->string('expertise')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('img_url')->nullable();
            $table->integer('PageId')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
