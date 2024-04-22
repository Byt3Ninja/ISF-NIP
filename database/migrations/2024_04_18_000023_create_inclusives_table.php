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
            Schema::create('inclusives', function (Blueprint $table) {
            $table->id();
            $table->string('img_url')->nullable();
            $table->string('en_title');
            $table->string('ar_title');

            $table->unsignedBigInteger('user_added')->nullable();
            $table->foreign('user_added')->references('id')->on('users')->onDelete('SET NULL');

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
        Schema::dropIfExists('inclusives');
    }
};
