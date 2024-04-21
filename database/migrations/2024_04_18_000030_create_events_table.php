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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title_en', 191);
            $table->string('title_ar', 191);
            $table->string('image', 191)->nullable();
            $table->text('body_ar');
            $table->text('body_en');
            $table->integer('showtimer')->nullable();
            $table->dateTime('timerdate')->nullable();
            $table->text('googlemap')->nullable();
            $table->string('published', 5)->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('events');
    }
};
