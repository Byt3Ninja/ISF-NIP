<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GV-10436 / GV-10440: founder-only fields (ISF). Nullable, so every other role and every
 * existing row is left as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'education_entity_id')) {
                $table->foreignId('education_entity_id')->nullable()->after('academic_stat')
                    ->constrained('education_entities')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'student_code')) {
                $table->string('student_code', 50)->nullable()->after('national_id');
            }
            if (! Schema::hasColumn('users', 'referral_source')) {
                $table->string('referral_source', 50)->nullable()->after('student_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'education_entity_id')) {
                $table->dropConstrainedForeignId('education_entity_id');
            }
            $table->dropColumn(array_values(array_filter(
                ['student_code', 'referral_source'],
                fn ($column) => Schema::hasColumn('users', $column)
            )));
        });
    }
};
