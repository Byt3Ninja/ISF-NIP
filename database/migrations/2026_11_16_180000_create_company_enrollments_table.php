<?php

use App\CompanyEnrollment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrollment history: one row for each time a company enters a cycle. Until now only
 * `companies.cycle` / `companies.step` existed, which hold the current cycle and are
 * blanked on rejection and on alumni marking. See App\CompanyEnrollment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_enrollments')) {
            Schema::create('company_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
                $table->foreignId('step_id')->nullable()->constrained('steps')->nullOnDelete();
                $table->string('status', 20)->default('active'); // active | completed | rejected | withdrawn
                $table->timestamp('joined_at')->nullable(); // empty for rows rebuilt from older data
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                // Not unique: a company that leaves a cycle and joins it again gets a second row.
                $table->index(['company_id', 'cycle_id', 'status']);
                $table->index(['cycle_id', 'status']);
            });
        }

        CompanyEnrollment::backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('company_enrollments');
    }
};
