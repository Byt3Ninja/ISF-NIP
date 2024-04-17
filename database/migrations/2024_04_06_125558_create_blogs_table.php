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
        Schema::create('blogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image')->default('');
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('blog_translations', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('blog_id')->unsigned();
            $table->string('title');
            $table->string('slug');
            $table->string('author')->nullable();
            $table->text('desc')->nullable();
            $table->text('short_desc')->nullable();
            $table->string('locale')->index();
            $table->unique(['blog_id','locale']);
            $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
        Schema::dropIfExists('blogs');
    }
};
