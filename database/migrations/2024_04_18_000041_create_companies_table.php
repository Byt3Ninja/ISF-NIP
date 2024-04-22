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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');

            $table->string('name_en');
            $table->string('name_ar');
            $table->string('sector', 200)->nullable();
            $table->string('address_en');
            $table->string('address_ar');
            $table->date('est_date')->nullable();
            $table->string('idea')->nullable();
            $table->string('problem')->nullable();
            $table->string('solution')->nullable();
            $table->integer('team_num')->nullable();
            $table->string('stage')->nullable();
            $table->string('raised_fund')->nullable();
            $table->string('investors')->nullable();
            $table->string('founder')->nullable();
            $table->string('employees')->nullable();
            $table->string('phone');
            $table->string('fax', 250)->nullable();
            $table->string('email');
            $table->string('website', 250)->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->text('google_map')->nullable();
            $table->string('logo', 250)->nullable();

            $table->unsignedBigInteger('cycle')->nullable();
            $table->foreign('cycle')->references('id')->on('cycles')->onDelete('set null');

            $table->unsignedBigInteger('step')->nullable();
            $table->foreign('step')->references('id')->on('steps')->onDelete('set null');


            $table->integer('approved')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');


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
        Schema::dropIfExists('companies');
    }
};
