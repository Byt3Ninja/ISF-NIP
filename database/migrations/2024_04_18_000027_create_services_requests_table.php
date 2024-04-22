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
        Schema::create('services_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('comment')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->string('approved', 3)->nullable();
            $table->integer('rate')->nullable();
            $table->string('rate_comment', 250)->nullable();
            $table->string('admin_comment')->nullable();

            $table->unsignedBigInteger('admin')->nullable();
            $table->foreign('admin')->references('id')->on('users')->onDelete('SET NULL');

            $table->integer('done')->nullable();
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
        Schema::dropIfExists('services_requests');
    }
};
