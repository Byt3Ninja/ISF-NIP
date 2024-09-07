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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('PUSHER_APP_KEY')->nullable()->after('BroadCast');
            $table->text('PUSHER_APP_ID')->nullable()->after('PUSHER_APP_KEY');
            $table->text('PUSHER_APP_SECRET')->nullable()->after('PUSHER_APP_ID');
            $table->text('PUSHER_APP_CLUSTER')->nullable()->after('PUSHER_APP_SECRET');
            $table->text('MIX_PUSHER_APP_KEY')->nullable()->after('PUSHER_APP_CLUSTER');
            $table->text('MIX_PUSHER_APP_CLUSTER')->nullable()->after('MIX_PUSHER_APP_KEY');

            $table->dropColumn('Secret_Key');
            $table->dropColumn('Client_ID');
            $table->dropColumn('Cluster_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('PUSHER_APP_KEY')->nullable();
            $table->text('PUSHER_APP_ID')->nullable();
            $table->text('PUSHER_APP_SECRET')->nullable();
            $table->text('PUSHER_APP_CLUSTER')->nullable();
            $table->text('MIX_PUSHER_APP_KEY')->nullable();
            $table->text('MIX_PUSHER_APP_CLUSTER')->nullable();

            $table->dropColumn('Secret_Key');
            $table->dropColumn('Client_ID');
            $table->dropColumn('Cluster_name');
        });
    }
};
