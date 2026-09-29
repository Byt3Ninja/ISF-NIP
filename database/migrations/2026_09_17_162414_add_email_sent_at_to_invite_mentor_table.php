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
        Schema::table('invite_mentor', function (Blueprint $table) {
            // Guards the invitation email against being sent more than once
            // for the same invitation record.
            $table->timestamp('email_sent_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invite_mentor', function (Blueprint $table) {
            $table->dropColumn('email_sent_at');
        });
    }
};
