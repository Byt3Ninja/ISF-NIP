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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('CreatedTax')->default(0)->comment('هل الشركة مسجلة');
            $table->string('Commercial_Register')->nullable()->comment('رقم السجل التجاري');
            $table->string('Tax_Card')->nullable()->comment('رقم البطاقة الضريبية');
            $table->unsignedInteger('Country_Register_id')->default(0)->comment('الدولة التي تم التسجيل فيها');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('CreatedTax')->default(0)->comment('هل الشركة مسجلة');
            $table->dropColumn('Commercial_Register')->nullable()->comment('رقم السجل التجارى');
            $table->dropColumn('Tax_Card')->nullable()->comment('رقم البطاقة الضريبية ');
            $table->dropColumn('Country_Register_id')->default(0)->comment('الدولة التى تم التسجيل فيها');
            
        });
    }
};
