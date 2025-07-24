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
        Schema::create('team_formations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the team formation');
            $table->text('description')->nullable()->comment('Description of the team formation');
            $table->unsignedBigInteger('cycle_id')->comment('ID of the cycle this team formation belongs to');
            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade')
                ->comment('Foreign key referencing the cycles table');
            $table->unsignedBigInteger('step_id')->comment('ID of the step this team formation belongs to');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade')
                ->comment('Foreign key referencing the steps table');
            $table->integer('max_teams_members')->default(0)->comment('Maximum number of teams allowed in this formation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_formation');
    }
};
