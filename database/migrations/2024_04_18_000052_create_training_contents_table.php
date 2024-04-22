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
        Schema::create('training_contents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('training_id');
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');

            $table->string('title', 191);
            $table->integer('arr')->default(0);
            $table->text('content');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('training_contents');
    }
};
