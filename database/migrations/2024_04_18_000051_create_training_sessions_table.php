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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('step_id');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('type', 10);
            $table->string('title', 191);
            $table->string('dsc', 2048)->nullable();
            $table->dateTime('datetime');
            $table->dateTime('enddatetime')->nullable();
            $table->integer('duration')->nullable();
            $table->string('zoom_id', 250)->nullable();
            $table->string('start_url', 1024)->nullable();
            $table->string('join_url', 1024)->nullable();
            $table->string('trainer_name', 250)->nullable();
            $table->string('location', 250)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_sessions');
    }
};
