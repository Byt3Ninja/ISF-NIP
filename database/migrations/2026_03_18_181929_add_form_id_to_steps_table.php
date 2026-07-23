<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->nullable()->after('data');
            $table->foreign('form_id')->references('id')->on('builder_forms')->onDelete('set null');
        });

        // Backfill: for screening (stype=2) and final screening (stype=5) steps,
        // find the form step (stype=1) at position arr-1 in the same cycle
        DB::statement("
            UPDATE steps AS s
            INNER JOIN steps AS f ON f.cycle_id = s.cycle_id AND f.stype = 1 AND f.arr = s.arr - 1
            INNER JOIN builder_forms AS bf ON bf.id = f.data
            SET s.form_id = bf.id
            WHERE s.stype IN (2, 5) AND s.form_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->dropColumn('form_id');
        });
    }
};
