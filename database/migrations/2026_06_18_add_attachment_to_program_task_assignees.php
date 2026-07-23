<?php

use App\ProgramTask;
use App\ProgramTaskAssignee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_task_assignees', function (Blueprint $table) {
            $table->json('attachment')->nullable()->after('status');
        });

        // Move existing submissions from program_tasks.attachments → program_task_assignees.attachment
        ProgramTask::whereNotNull('attachments')->each(function ($task) {
            $allAtts = $task->attachments ?? [];

            $submissions    = array_filter($allAtts, fn($a) => ($a['status'] ?? '') === 'submission');
            $nonSubmissions = array_values(array_filter($allAtts, fn($a) => ($a['status'] ?? '') !== 'submission'));

            if (empty($submissions)) return;

            // Group by company_id and move to assignee rows
            $grouped = collect($submissions)->groupBy('company_id');
            foreach ($grouped as $companyId => $files) {
                ProgramTaskAssignee::where('program_task_id', $task->id)
                    ->where('company_id', $companyId)
                    ->update(['attachment' => json_encode($files->values()->toArray())]);
            }

            // Remove submissions from program_tasks.attachments
            $task->update(['attachments' => $nonSubmissions]);
        });
    }

    public function down(): void
    {
        Schema::table('program_task_assignees', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
