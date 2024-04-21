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
        Schema::create('messenger', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sender');
            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('receiver');
            $table->foreign('receiver')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('messgeId')->nullable();
            $table->foreign('messgeId')->references('id')->on('messenger')->onDelete('cascade');

            $table->string('subject', 191)->nullable();
            $table->text('message')->nullable();
            $table->boolean('read')->nullable();
            $table->integer('reported')->nullable();

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
        Schema::dropIfExists('messenger');
    }
};
