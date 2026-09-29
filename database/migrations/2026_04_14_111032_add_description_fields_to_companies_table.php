<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('achievements')->nullable()->after('products_services_description');
            $table->text('mission')->nullable()->after('achievements');
            $table->text('why_this_startup')->nullable()->after('mission');
            $table->text('startup_expertise')->nullable()->after('why_this_startup');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'achievements',
                'mission',
                'why_this_startup',
                'startup_expertise',
            ]);
        });
    }
};
