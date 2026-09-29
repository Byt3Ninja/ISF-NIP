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
        Schema::table('service_providers', function (Blueprint $table) {
            $table->boolean('show_stats')->default(true)->after('operating_region');
            $table->string('stat1_label', 50)->nullable()->after('show_stats');
            $table->string('stat1_value', 20)->nullable()->after('stat1_label');
            $table->string('stat2_label', 50)->nullable()->after('stat1_value');
            $table->string('stat2_value', 20)->nullable()->after('stat2_label');
            $table->string('stat3_label', 50)->nullable()->after('stat2_value');
            $table->string('stat3_value', 20)->nullable()->after('stat3_label');
            $table->string('stat4_label', 50)->nullable()->after('stat3_value');
            $table->string('stat4_value', 20)->nullable()->after('stat4_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn([
                'show_stats',
                'stat1_label', 'stat1_value',
                'stat2_label', 'stat2_value',
                'stat3_label', 'stat3_value',
                'stat4_label', 'stat4_value'
            ]);
        });
    }
};
