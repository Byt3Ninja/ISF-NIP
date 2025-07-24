<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rows = DB::table('training_sessions')->select('id', 'datetime', 'enddatetime')->get();
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->string('datetime', 20)->nullable()->change();
            $table->string('enddatetime', 20)->nullable()->change();
        });

         foreach ($rows as $row) {
            DB::table('training_sessions')->where('id', $row->id)->update([
                'datetime' => $row->datetime ? Carbon::parse($row->datetime)->timestamp  : null,
                'enddatetime' => $row->enddatetime ? Carbon::parse($row->enddatetime)->timestamp : null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->date('datetime')->nullable()->change();
            $table->date('enddatetime')->nullable()->change();
        });
    }
};
