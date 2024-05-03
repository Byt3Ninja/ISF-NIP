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
        Schema::create('mentorship_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mentor_id');
            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('comment')->nullable();
            $table->string('approved', 3)->nullable();
            $table->string('admin_comment')->nullable();

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('fileurl', 250)->nullable();
            $table->string('start_url', 1024)->nullable();
            $table->string('join_url', 1024)->nullable();
            $table->dateTime('zoom_date')->nullable();
            $table->integer('period')->default(0);
            $table->integer('rating')->nullable();
            $table->string('rate_comment', 250)->nullable();
            $table->integer('done')->nullable();

            $table->unsignedBigInteger('cycle_id');
            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');

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
        Schema::dropIfExists('mentorship_requests');
    }
};
