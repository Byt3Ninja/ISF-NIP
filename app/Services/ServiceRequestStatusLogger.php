<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ServiceRequestStatusLogger
{
    /**
     * Log a status change for a service request.
     *
     * @param int         $serviceRequestId
     * @param string      $status       One of: 'pending', 'approved', 'rejected', 'completed'
     * @param int|null    $causerId     The user who triggered this status change
     * @param string|null $comment
     * @param string|null $occurredAt   ISO timestamp; defaults to now()
     */
    public static function log(
        int     $serviceRequestId,
        string  $status,
        ?int    $causerId = null,
        ?string $comment = null,
        ?string $occurredAt = null
    ): void {
        DB::table('service_request_status_logs')->insert([
            'service_request_id' => $serviceRequestId,
            'status'             => $status,
            'comment'            => $comment,
            'causer_id'          => $causerId,
            'occurred_at'        => $occurredAt ?? now(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }
}
