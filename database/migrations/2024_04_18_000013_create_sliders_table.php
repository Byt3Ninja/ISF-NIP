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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('img_path');
            $table->string('title', 250);
            $table->string('ar_title', 250);
            $table->string('img_desc')->nullable();
            $table->string('img_url')->nullable();
            $table->string('url_title')->nullable();
            $table->string('ar_url_title')->nullable();
            $table->string('ar_img_desc')->nullable();
            $table->integer('slideorder');

            $table->unsignedBigInteger('user_added')->nullable();
            $table->foreign('user_added')->references('id')->on('users')->onDelete('SET NULL');

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
        Schema::dropIfExists('sliders');
    }
};
