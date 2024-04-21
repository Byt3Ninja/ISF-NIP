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
        Schema::create('cycles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('incubator_id')->default(1);
            $table->string('title', 250)->nullable();
            $table->text('description')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->string('private', 250)->nullable();

            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('cycles');
    }
};
