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
            $table->boolean('accept_email')->default(true)->after('timezone');
            $table->boolean('accept_whatsapp')->default(false)->after('accept_email');
            $table->boolean('accept_pusher')->default(false)->after('accept_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accept_email', 'accept_whatsapp', 'accept_pusher']);
        });
    }
};
