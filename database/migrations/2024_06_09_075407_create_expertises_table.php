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
        Schema::create('expertises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('expertises')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('mentor_expertise', function(Blueprint $table)
        {
            $table->bigInteger('expertise_id')->unsigned();
            $table->foreign('expertise_id')->references('id')->on('expertises')->onDelete('cascade');
            $table->bigInteger('mentor_id')->unsigned();
            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_expertise');
        Schema::dropIfExists('expertises');
    }
};
