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
            $table->string('country_code')->nullable()->after('age');
            $table->string('country_code_w_a')->nullable()->after('phone');
            $table->string('whatsapp', 20)->nullable()->after('country_code_w_a'); // إضافة الحقل بعد حقل الهاتف (phone) إذا كان موجودًا
            $table->integer('whatsapp_verified')->default(0)->after('whatsapp'); // إضافة الحقل بعد حقل الهاتف (phone) إذا كان موجودًا
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->dropColumn('country_code_w_a');
            $table->dropColumn('whatsapp');
            $table->dropColumn('whatsapp_verified');
        });
    }
};
