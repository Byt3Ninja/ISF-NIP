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
            // The new_email_requested_at value the confirmation email was last sent
            // for. A retry of the same request is skipped; a new request or an
            // explicit resend bumps new_email_requested_at and sends again.
            $table->timestamp('email_change_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_change_notified_at');
        });
    }
};
