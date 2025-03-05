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
        Schema::table('services', function (Blueprint $table) {
            $table->string('vendor_name')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->boolean('hide_price')->default(false);
            $table->boolean('hide_vendor_info')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_name',
                'vendor_email',
                'vendor_phone',
                'hide_price',
                'hide_vendor_info',
            ]);
        });
    }
};
