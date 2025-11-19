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
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('id');
            $table->string('employment_type')->nullable()->after('gender');
            $table->date('hiring_date')->nullable()->after('employment_type');
            $table->json('documents')->nullable()->after('hiring_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['gender', 'employment_type', 'hiring_date', 'documents']);

        });
    }
};
