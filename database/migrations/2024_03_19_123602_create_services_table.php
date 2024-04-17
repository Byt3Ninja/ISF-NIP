<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('image')->default('');
            $table->string('icon')->default('');
            $table->string('video_url')->nullable();
            $table->timestamps();
        });

        Schema::create('service_translations', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('service_id')->unsigned();
            $table->string('title');
            $table->string('slug');
            $table->string('short_desc')->nullable();
            $table->text('desc')->nullable();
            $table->string('locale')->index();
            $table->unique(['service_id','locale']);
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_translations');
        Schema::dropIfExists('services');

    }
};
