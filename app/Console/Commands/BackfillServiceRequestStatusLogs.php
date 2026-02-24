<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillServiceRequestStatusLogs extends Command
{
    protected $signature = 'service-requests:backfill-status-logs';

    protected $description = 'Backfill status log entries from existing service request data';

    public function handle()
    {
        $requests = DB::table('services_requests')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $bar = $this->output->createProgressBar($requests->count());
        $inserted = 0;

        foreach ($requests as $sr) {
            // Skip if already has logs (idempotent)
            $existingCount = DB::table('service_request_status_logs')
                ->where('service_request_id', $sr->id)
                ->count();

            if ($existingCount > 0) {
                $bar->advance();
                continue;
            }

            $logs = [];
            $now = now();

            // 1. Created (pending)
            $logs[] = [
                'service_request_id' => $sr->id,
                'status'             => 'pending',
                'comment'            => $sr->comment,
                'causer_id'          => $sr->user_id,
                'occurred_at'        => $sr->created_at,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            // 2. Approved
            if ($sr->approved === 'Yes') {
                $logs[] = [
                    'service_request_id' => $sr->id,
                    'status'             => 'approved',
                    'comment'            => $sr->admin_comment,
                    'causer_id'          => $sr->admin,
                    'occurred_at'        => $sr->decided_at ?? $sr->updated_at,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            // 3. Rejected
            if ($sr->approved === 'No') {
                $logs[] = [
                    'service_request_id' => $sr->id,
                    'status'             => 'rejected',
                    'comment'            => $sr->admin_comment,
                    'causer_id'          => $sr->admin,
                    'occurred_at'        => $sr->decided_at ?? $sr->updated_at,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            // 4. Completed
            if ($sr->done == 1) {
                $completedAt = $sr->decided_at
                    ? \Carbon\Carbon::parse($sr->decided_at)->addMinutes(1)
                    : $sr->updated_at;

                $logs[] = [
                    'service_request_id' => $sr->id,
                    'status'             => 'completed',
                    'comment'            => null,
                    'causer_id'          => $sr->user_id,
                    'occurred_at'        => $completedAt,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            DB::table('service_request_status_logs')->insert($logs);
            $inserted += count($logs);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Backfill complete. Inserted {$inserted} log entries for {$requests->count()} requests.");

        return Command::SUCCESS;
    }
}
