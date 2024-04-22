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
        Schema::create('steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cycle_id')->constrained()->onDelete('cascade');

            $table->string('title', 250)->nullable();
            $table->text('description')->nullable();
            $table->integer('stype');
            $table->string('data', 250)->nullable();
            $table->longText('list')->nullable();
            $table->integer('arr');
            $table->date('from')->nullable();
            $table->date('to')->nullable();

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
        Schema::dropIfExists('steps');
    }
};
