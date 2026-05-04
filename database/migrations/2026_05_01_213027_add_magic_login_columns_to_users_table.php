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
            $table->string('magic_login_token', 64)->nullable()->index();
            $table->timestamp('magic_login_sent_at')->nullable();
            $table->boolean('must_change_password')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['magic_login_token']);
            $table->dropColumn(['magic_login_token', 'magic_login_sent_at', 'must_change_password']);
        });
    }
};
