<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('address_en', 255)->nullable()->change();
            $table->string('address_ar', 255)->nullable()->change();
        });

        // Normalise the importer's placeholder single-space entries to NULL so
        // downstream queries don't have to special-case " ".
        DB::table('companies')->where('address_en', ' ')->update(['address_en' => null]);
        DB::table('companies')->where('address_ar', ' ')->update(['address_ar' => null]);
    }

    public function down(): void
    {
        // Backfill NULLs to empty string before reinstating NOT NULL.
        DB::table('companies')->whereNull('address_en')->update(['address_en' => '']);
        DB::table('companies')->whereNull('address_ar')->update(['address_ar' => '']);

        Schema::table('companies', function (Blueprint $table) {
            $table->string('address_en', 255)->nullable(false)->change();
            $table->string('address_ar', 255)->nullable(false)->change();
        });
    }
};
