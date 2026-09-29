<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('previous_incubation', 1)->nullable()->after('info_company');
            $table->string('previous_incubator', 150)->nullable()->after('previous_incubation');
            $table->string('company_registration_type', 100)->nullable()->after('previous_incubator');
            $table->string('products_services_description', 1000)->nullable()->after('company_registration_type');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'previous_incubation',
                'previous_incubator',
                'company_registration_type',
                'products_services_description',
            ]);
        });
    }
};
