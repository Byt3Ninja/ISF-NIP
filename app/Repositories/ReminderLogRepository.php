<?php

namespace App\Repositories;

use App\Enums\ReminderLogStatus;
use App\ReminderLog;

class ReminderLogRepository
{
    /**
     * Bulk fetch sent / skipped reminder log lookup keys for a list of cycle IDs.
     * Returns array map: ["{$userId}_{$type}_{$cycleId}_{$requestId}_{$scheduleId}" => true]
     *
     * @param array $cycleIds
     * @return array<string, bool>
     */
    public function getSentReminderKeysForCycles(array $cycleIds): array
    {
        if (empty($cycleIds)) {
            return [];
        }

        $logs = ReminderLog::whereIn('cycle_id', array_unique($cycleIds))
            ->whereIn('status', [ReminderLogStatus::SENT->value, ReminderLogStatus::SKIPPED->value])
            ->get(['user_id', 'type', 'cycle_id', 'meta']);

        $sentKeys = [];
        foreach ($logs as $log) {
            $meta = is_array($log->meta) ? $log->meta : json_decode($log->meta ?? '', true);
            if (is_array($meta)) {
                $requestId = $meta['mentorship_request_id'] ?? null;
                $scheduleId = $meta['schedule_id'] ?? null;

                if ($requestId && $scheduleId) {
                    $key = "{$log->user_id}_{$log->type}_{$log->cycle_id}_{$requestId}_{$scheduleId}";
                    $sentKeys[$key] = true;
                }
            }
        }

        return $sentKeys;
    }

    /**
     * Check if a reminder log has already been sent for a specific user, request, and schedule.
     *
     * @param int $userId
     * @param int $cycleId
     * @param string $type ('Mentor' or 'User')
     * @param int $requestId
     * @param int $scheduleId
     * @return bool
     */
    public function hasReminderBeenSent(int $userId, int $cycleId, string $type, int $requestId, int $scheduleId): bool
    {
        return ReminderLog::where('user_id', $userId)
            ->where('cycle_id', $cycleId)
            ->where('type', $type)
            ->where('status', 'sent')
            ->where(function ($q) use ($requestId, $scheduleId) {
                $q->whereJsonContains('meta->mentorship_request_id', $requestId)
                  ->whereJsonContains('meta->schedule_id', $scheduleId);
            })
            ->exists();
    }

    /**
     * Create a new reminder log entry.
     *
     * @param int $userId
     * @param string $type
     * @param int $cycleId
     * @param string $channel
     * @param string $status
     * @param array $meta
     * @return ReminderLog
     */
    public function createLog(int $userId, string $type, int $cycleId, string $channel, string $status, array $meta): ReminderLog
    {
        return ReminderLog::create([
            'user_id'  => $userId,
            'type'     => $type,
            'cycle_id' => $cycleId,
            'channel'  => $channel,
            'status'   => $status,
            'meta'     => $meta,
        ]);
    }

    /**
     * Bulk insert reminder logs in one query.
     *
     * @param array $rows Each row: user_id, type, cycle_id, channel, status, meta (array)
     */
    public function insertLogs(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $now = now();

        ReminderLog::insert(array_map(fn (array $row) => array_merge($row, [
            'meta' => json_encode($row['meta'] ?? null),
            'created_at' => $now,
            'updated_at' => $now,
        ]), $rows));
    }
}
