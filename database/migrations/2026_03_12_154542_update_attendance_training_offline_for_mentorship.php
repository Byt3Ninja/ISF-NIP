<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAttendanceTrainingOfflineForMentorship extends Migration
{
    public function up()
    {
        Schema::table('attendance_training_offline', function (Blueprint $table) {
            $table->foreignId('training_session_id')->nullable()->change();
            $table->foreignId('step_id')->nullable()->change();
            $table->foreignId('cycle_id')->nullable()->change();
            $table->foreignId('mentorship_request_id')->nullable()->after('training_session_id')
                ->constrained('mentorship_requests')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('attendance_training_offline', function (Blueprint $table) {
            $table->dropForeign(['mentorship_request_id']);
            $table->dropColumn('mentorship_request_id');
            $table->foreignId('training_session_id')->nullable(false)->change();
            $table->foreignId('step_id')->nullable(false)->change();
            $table->foreignId('cycle_id')->nullable(false)->change();
        });
    }
}
