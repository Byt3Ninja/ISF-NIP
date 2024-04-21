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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();

            $table->integer('calendarId')->nullable()->default(1);
            $table->string('title', 250);
            $table->string('body', 250)->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->boolean('allDay')->nullable();
            $table->string('category', 250)->nullable();
            $table->string('location', 250)->nullable();
            $table->boolean('isReadOnly')->nullable();
            $table->boolean('isPrivate')->nullable();
            $table->string('color', 250)->nullable()->default('#3a87ad');

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('calendars');
    }
};
