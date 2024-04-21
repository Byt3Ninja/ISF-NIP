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
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->nullable();

            $table->unsignedBigInteger('internal_id')->nullable();
            $table->foreign('internal_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('email', 191)->nullable();
            $table->string('phone', 250)->nullable();
            $table->string('subject', 250)->nullable();
            $table->text('message')->nullable();
            $table->boolean('read')->nullable()->default(false);
            $table->string('userip', 250)->nullable();

            $table->unsignedBigInteger('user_id')->default(0);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('replyBy')->nullable();
            $table->string('reSubject', 250)->nullable();
            $table->string('reMessage', 1024)->nullable();

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
        Schema::dropIfExists('messages');
    }
};
