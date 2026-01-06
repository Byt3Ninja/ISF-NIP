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
        Schema::table('milestones', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('status')->default('Not Started')->after('category');
            $table->boolean('completed')->default(0)->after('status');
            $table->date('milestone_date')->nullable()->after('completed');
            $table->string('visibility')->nullable()->comment('Public or Private')->after('milestone_date');
            $table->string('Cohort')->nullable()->after('visibility');
            $table->string('attachment')->nullable()->after('Cohort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'status',
                'completed',
                'milestone_date',
                'visibility',
                'attachment'
            ]);
        });
    }
};
