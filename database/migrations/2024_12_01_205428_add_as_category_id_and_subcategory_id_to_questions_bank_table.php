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
        Schema::table('questions_bank', function (Blueprint $table) {
            $table->unsignedBigInteger('ascategory_id')->nullable()->after('keywords'); // إضافة عمود asCategory_id بعد id
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('ascategory_id'); // إضافة عمود subcategory_id بعد asCategory_id
            $table->longText('explanation_QA_ar')->nullable()->after('explanation')->comment('شرح السؤال باللغة العربية');
            $table->longText('explanation_QA_en')->nullable()->after('explanation_QA_ar')->comment('شرح السؤال باللغة الانجليزية');
            $table->longText('explanation_AA_ar')->nullable()->after('explanation_QA_en')->comment('شرح الجواب باللغة العربية');
            $table->longText('explanation_AA_en')->nullable()->after('explanation_AA_ar')->comment('شرح الجواب باللغة الانجليزية');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions_bank', function (Blueprint $table) {
            $table->dropColumn(['asCategory_id', 'subcategory_id','explanation_QA_ar','explanation_QA_en','explanation_AA_ar','explanation_AA_en']);
        });
    }
};
