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
        Schema::create('solutions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('main_video')->nullable();
            $table->boolean('has_mobile')->default(false);
            $table->boolean('has_site')->default(false);
            $table->boolean('has_dash')->default(false);
            $table->boolean('mobile_video')->nullable();
            $table->boolean('site_video')->nullable();
            $table->boolean('dash_video')->nullable();
            $table->string('mobile_image_1')->nullable();
            $table->string('mobile_image_2')->nullable();
            $table->string('site_image_1')->nullable();
            $table->string('site_image_2')->nullable();
            $table->string('dash_image_1')->nullable();
            $table->string('dash_image_2')->nullable();
            $table->timestamps();
        });

        Schema::create('solution_translations', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('solution_id')->unsigned();
            $table->string('title');
            $table->string('slug');
            $table->string('short_desc')->nullable();
            $table->text('desc')->nullable();
            $table->string('features')->nullable();
            $table->string('feature_sec_desc')->nullable();
            $table->string('dep_sec_desc')->nullable();
            $table->string('mobile_desc')->nullable();
            $table->string('site_desc')->nullable();
            $table->string('dash_desc')->nullable();
            $table->string('locale')->index();
            $table->unique(['solution_id','locale']);
            $table->foreign('solution_id')->references('id')->on('solutions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solution_translations');
        Schema::dropIfExists('solutions');
    }
};
