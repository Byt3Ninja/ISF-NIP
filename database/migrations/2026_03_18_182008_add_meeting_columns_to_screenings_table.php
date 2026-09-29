<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->string('meeting_password')->nullable()->after('meeting_link');
            $table->string('zoom_id')->nullable()->after('meeting_password');
            $table->text('start_url')->nullable()->after('zoom_id');
        });
    }

    public function down(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->dropColumn(['meeting_password', 'zoom_id', 'start_url']);
        });
    }
};
