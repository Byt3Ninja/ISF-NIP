<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_request_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id');
            $table->string('status', 20);
            $table->string('comment', 500)->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('service_request_id')
                  ->references('id')
                  ->on('services_requests')
                  ->onDelete('cascade');

            $table->foreign('causer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index(['service_request_id', 'occurred_at']);
        });

        // Backfill existing service requests
        $this->backfill();
    }

    /**
     * Backfill status log entries from existing service request data.
     */
    private function backfill(): void
    {
        $requests = DB::table('services_requests')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        foreach ($requests as $sr) {
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_status_logs');
    }
};
