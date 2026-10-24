<?php

namespace App\Services\Importer;

class ImporterResult
{
    public int $processed;
    public int $imported;
    public int $skipped;
    public int $failed;
    public array $failedRows;
    public array $skippedRows;
    /** Imported rows whose welcome/credentials email could not be sent. */
    public int $emailFailed;
    public array $emailFailedRows;

    public function __construct(
        int $processed,
        int $imported,
        int $skipped,
        int $failed,
        array $failedRows,
        array $skippedRows,
        int $emailFailed = 0,
        array $emailFailedRows = []
    ) {
        $this->processed   = $processed;
        $this->imported    = $imported;
        $this->skipped     = $skipped;
        $this->failed      = $failed;
        $this->failedRows  = $failedRows;
        $this->skippedRows = $skippedRows;
        $this->emailFailed = $emailFailed;
        $this->emailFailedRows = $emailFailedRows;
    }

    public function statusMessageKey(): string
    {
        if ($this->imported === 0) {
            return 'importer.ui.results.failed';
        }
        if ($this->skipped > 0 || $this->failed > 0 || $this->emailFailed > 0) {
            return 'importer.ui.results.completed_with_skipped';
        }
        return 'importer.ui.results.completed';
    }

    public function toArray(): array
    {
        $key = $this->statusMessageKey();
        return [
            'status_message_en' => __($key, [], 'en'),
            'status_message_ar' => __($key, [], 'ar'),
            'totals' => [
                'processed' => $this->processed,
                'imported'  => $this->imported,
                'skipped'   => $this->skipped,
                'failed'    => $this->failed,
                'email_failed' => $this->emailFailed,
            ],
            'failed_rows'  => $this->failedRows,
            'skipped_rows' => $this->skippedRows,
            'email_failed_rows' => $this->emailFailedRows,
        ];
    }
}
