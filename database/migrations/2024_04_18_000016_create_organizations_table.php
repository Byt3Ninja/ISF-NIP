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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone', 100)->nullable();
            $table->text('bio')->nullable();


            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');

            $table->boolean('status')->nullable()->default(true);
            $table->string('api_token')->nullable();
            $table->string('color')->default('');
            $table->string('img_url')->default('');

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
        Schema::dropIfExists('organizations');
    }
};
