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
        if( !Schema::hasTable('project_likes') )
        {
            Schema::create('project_likes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_id')->nullable()->default(null);
                $table->unsignedBigInteger('user_id')->nullable()->default(null);

                $table->foreign('team_id')->references('id')->on('team_formations')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_likes');
    }
};
