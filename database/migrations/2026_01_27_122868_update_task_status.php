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
        //update enum status 
        Schema::table('program_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed','review','progress'])->default('pending')->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //rollback enum status 
        Schema::table('program_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed','review','progress'])->default('pending')->change();
        });
    }
};
