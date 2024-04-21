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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('type_trans');
            $table->double('price', null, 0);
            $table->double('discount', null, 0);
            $table->text('desc_en');
            $table->text('desc_ar');
            $table->string('published')->nullable();
            $table->string('video_link');

            $table->unsignedBigInteger('user_added')->nullable();
            $table->foreign('user_added')->references('id')->on('users')->onDelete('set null');

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
        Schema::dropIfExists('rooms');
    }
};
