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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title', 250);
            $table->string('ar_title', 250);
            $table->text('dsc')->nullable();
            $table->text('ar_dsc')->nullable();
            $table->text('set1')->nullable();
            $table->text('ar_set1')->nullable();
            $table->text('set2')->nullable();
            $table->text('ar_set2')->nullable();
            $table->string('url')->nullable();
            $table->string('img_url')->nullable();
            $table->integer('set_order')->nullable();

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
        Schema::dropIfExists('settings');
    }
};
