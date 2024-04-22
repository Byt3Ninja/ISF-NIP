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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('HaveCompany')->nullable()->default(0)->after('title_en');
            $table->unsignedBigInteger('company_id')->nullable()->after('HaveCompany');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->unsignedBigInteger('organization_id')->nullable()->after('company_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['HaveCompany','company_id','organization_id']);
        });
    }
};
