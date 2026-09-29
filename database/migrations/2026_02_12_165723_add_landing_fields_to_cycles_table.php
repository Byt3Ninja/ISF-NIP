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
        Schema::table('cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('cycles', 'short_description')) {
                $table->string('short_description', 500)->nullable()->after('description');
            }
            if (!Schema::hasColumn('cycles', 'location')) {
                $table->string('location', 255)->nullable()->after('end');
            }
            if (!Schema::hasColumn('cycles', 'landing_visible')) {
                $table->boolean('landing_visible')->default(false)->after('is_public');
            }
            if (!Schema::hasColumn('cycles', 'landing_sort_order')) {
                $table->integer('landing_sort_order')->nullable()->after('landing_visible');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $columns = ['short_description', 'location', 'landing_visible', 'landing_sort_order'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cycles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
