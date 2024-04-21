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
        Schema::create('incubators', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250)->nullable();
            $table->string('description', 250)->nullable();
            $table->date('establishmentdate')->nullable();
            $table->string('portfolio', 250)->nullable();
            $table->string('partners', 500)->nullable();
            $table->string('offering', 500)->nullable();
            $table->string('logo', 250)->nullable();
            $table->string('facebook', 250)->nullable();
            $table->string('twitter', 250)->nullable();
            $table->string('linkeded', 250)->nullable();
            $table->string('youtube', 250)->nullable();
            $table->string('website', 250)->nullable();
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
        Schema::dropIfExists('incubators');
    }
};
