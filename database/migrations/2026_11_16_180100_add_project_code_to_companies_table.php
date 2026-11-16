<?php

use App\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A project number for every company, given once and never changed, so the same
 * project can be recognised across cycles and by other funders. New companies get
 * theirs in Company's `created` hook; existing ones are numbered here.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'project_code')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('project_code', 24)->nullable()->unique()->after('id');
            });
        }

        Company::backfillProjectCodes();
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'project_code')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropUnique(['project_code']);
                $table->dropColumn('project_code');
            });
        }
    }
};
