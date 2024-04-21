<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_completed', function (Blueprint $table) {
            $table->unsignedBigInteger('training_id');
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');

            $table->integer('user_id');
            $table->integer('course_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_completed');
    }
};
