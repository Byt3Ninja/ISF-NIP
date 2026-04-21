<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (!Schema::hasColumn('team_members', 'national_id')) {
                $table->string('national_id', 64)->nullable()->after('Email');
            }
            if (!Schema::hasColumn('team_members', 'educational_status')) {
                $table->string('educational_status', 20)->nullable()->after('national_id');
            }
            if (!Schema::hasColumn('team_members', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('educational_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            foreach (['national_id', 'educational_status', 'date_of_birth'] as $c) {
                if (Schema::hasColumn('team_members', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
