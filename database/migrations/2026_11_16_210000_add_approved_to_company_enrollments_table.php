<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `companies.approved` / `approved_by` say whether staff have let a company past its
 * current step. A company active in several cycles needs that answer per cycle, so
 * each enrollment carries its own copy. See Company::switchToEnrollment().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_enrollments', 'approved')) {
            Schema::table('company_enrollments', function (Blueprint $table) {
                $table->integer('approved')->nullable()->after('status');
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved');
            });
        }

        // Existing active rows take the value their company holds for that cycle today.
        DB::statement(
            "UPDATE company_enrollments e
               JOIN companies c ON c.id = e.company_id AND c.cycle = e.cycle_id
                SET e.approved = c.approved, e.approved_by = c.approved_by
              WHERE e.status = 'active'"
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_enrollments', 'approved')) {
            Schema::table('company_enrollments', function (Blueprint $table) {
                $table->dropColumn(['approved', 'approved_by']);
            });
        }
    }
};
