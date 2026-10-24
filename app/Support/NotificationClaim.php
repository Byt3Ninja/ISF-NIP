<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * A "this notification was sent" stamp, claimed atomically before sending and released
 * again when the send fails, so a duplicate request can never send twice and a failed
 * send can still be retried.
 *
 *   NotificationClaim::on('mentorship_requests', ['id' => $id], 'mentor_notified_at')
 *       ->run(fn ($claim) => $mentor->notify((new SomeNotification(...))->withClaim($claim)));
 *
 * run() releases the claim when the send throws. A queued notification fails later, on the
 * worker, so it carries the claim (ReleasesNotificationClaim) and releases it in failed().
 * Plain value object: it is serialized into queued notifications.
 */
final class NotificationClaim
{
    /**
     * @param  array<string, mixed>  $where  column => value (null means IS NULL)
     * @param  bool  $versioned  also re-claim a row stamped with a different value
     *                           (e.g. the stamp is the version of the change it announced)
     */
    public function __construct(
        public string $table,
        public array $where,
        public string $column,
        public string $value,
        public bool $versioned = false,
    ) {
    }

    public static function on(string $table, array $where, string $column, $value = null, bool $versioned = false): self
    {
        return new self($table, $where, $column, (string) ($value ?? now()), $versioned);
    }

    /** Stamp the row(s); false when another caller already holds the claim. */
    public function take(): bool
    {
        return $this->query()
            ->where(function ($q) {
                $q->whereNull($this->column);
                if ($this->versioned) {
                    $q->orWhere($this->column, '!=', $this->value);
                }
            })
            ->update([$this->column => $this->value]) > 0;
    }

    /** Undo this claim (only the rows it stamped), so the notification can be sent again. */
    public function release(): void
    {
        $this->query()->where($this->column, $this->value)->update([$this->column => null]);
    }

    /**
     * Take the claim and run $send($this). Returns false when already claimed. If the send
     * throws, the claim is released and the exception rethrown.
     */
    public function run(callable $send): bool
    {
        if (!$this->take()) {
            return false;
        }

        try {
            $send($this);
        } catch (\Throwable $e) {
            $this->release();
            throw $e;
        }

        return true;
    }

    private function query()
    {
        $query = DB::table($this->table);
        foreach ($this->where as $column => $value) {
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }

        return $query;
    }
}
