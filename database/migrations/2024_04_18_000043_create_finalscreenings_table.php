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
        Schema::create('finalscreenings', function (Blueprint $table) {

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('step_id');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');

            $table->string('judges', 500);
            $table->dateTime('datetime')->nullable();
            $table->integer('duration')->nullable();
            $table->string('zoom_id', 250)->nullable();
            $table->string('start_url', 1024)->nullable();
            $table->string('join_url', 1024)->nullable();

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
        Schema::dropIfExists('finalscreenings');
    }
};
