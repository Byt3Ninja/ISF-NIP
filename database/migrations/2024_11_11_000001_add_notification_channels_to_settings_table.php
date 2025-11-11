<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add notification channel settings if they don't exist
            if (!Schema::hasColumn('settings', 'enable_email')) {
                $table->boolean('enable_email')->default(true);
            }
            if (!Schema::hasColumn('settings', 'enable_whatsapp')) {
                $table->boolean('enable_whatsapp')->default(true);
            }
            if (!Schema::hasColumn('settings', 'enable_pusher')) {
                $table->boolean('enable_pusher')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_email',
                'enable_whatsapp',
                'enable_pusher',
            ]);
        });
    }
};
