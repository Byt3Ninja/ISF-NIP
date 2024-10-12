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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3); // VARCHAR(3) لتخزين كود العملة
            $table->string('currency_name', 100); // VARCHAR(100) لتخزين اسم العملة
            $table->string('currency_symbol', 10)->nullable(); // VARCHAR(10) لتخزين رمز العملة
            $table->decimal('exchange_rate_to_usd', 10, 6)->nullable(); // DECIMAL(10,6) لتخزين معدل التحويل للدولار
            $table->boolean('enabled')->default(true); // BOOLEAN لتحديد إذا كانت العملة مفعلة
            $table->string('flag', 255)->nullable(); // VARCHAR(255) لتخزين رابط الصورة أو مسار العلم
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate(); // TIMESTAMP لتسجيل آخر تحديث مع تحديث تلقائي
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
