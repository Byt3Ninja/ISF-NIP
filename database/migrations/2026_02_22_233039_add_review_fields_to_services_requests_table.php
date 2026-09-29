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
        Schema::table('services_requests', function (Blueprint $table) {
            $table->string('review_title', 50)->nullable()->after('rate_comment');
            $table->text('review_body')->nullable()->after('review_title');
            $table->timestamp('reviewed_at')->nullable()->after('review_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services_requests', function (Blueprint $table) {
            $table->dropColumn(['review_title', 'review_body', 'reviewed_at']);
        });
    }
};
