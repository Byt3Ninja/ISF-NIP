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
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country');
            $table->string('image')->default('');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->string('ios')->nullable();
            $table->string('android')->nullable();
            $table->string('website')->nullable();
            $table->string('design')->nullable();
            $table->string('downloads')->default('0');
            $table->timestamps();
        });

        Schema::create('project_translations', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('project_id')->unsigned();
            $table->string('title');
            $table->string('slug');
            $table->string('short_desc')->nullable();
            $table->string('client_name')->nullable();
            $table->text('desc')->nullable();
            $table->string('locale')->index();
            $table->unique(['project_id','locale']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
        Schema::create('project_service', function(Blueprint $table)
        {
            $table->bigInteger('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_service');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
    }
};
