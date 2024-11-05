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
        Schema::create('time_sheets', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key to users
            $table->date('date')->nullable(); // Date of the entry
            $table->float('hours_worked')->nullable(); // Number of hours worked
            $table->text('description')->nullable(); // Description of the work done
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key to categories table
            $table->unsignedBigInteger('program_id')->nullable(); // Foreign key to programs table
            $table->integer('status')->default(0)->comment('if 0=Draft and 1=Submitted'); // Status of the Time Sheet table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_sheets');
    }
};
