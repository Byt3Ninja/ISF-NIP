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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->tinyText('plan')->nullable();
            $table->tinyText('name')->nullable();
            $table->tinyText('description')->nullable();
            $table->unsignedBigInteger('Company_id');
            $table->timestamps();
            $table->softDeletes();
                // Foreign key constraint
            $table->foreign('Company_id')->references('id')->on('companies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
