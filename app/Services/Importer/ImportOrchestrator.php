<?php

namespace App\Services\Importer;

use App\Organization;
use App\Services\ProgramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportOrchestrator
{
    private ImporterSessionService $svc;
    private CsvParser $parser;
    private RowValidator $validator;
    private ConflictDetector $conflictDetector;
    private ProgramService $programService;
    private RowImporter $rowImporter;
    private ImporterEmailDispatcher $emailDispatcher;

    public function __construct(
        ImporterSessionService $svc,
        CsvParser $parser,
        RowValidator $validator,
        ConflictDetector $conflictDetector,
        ProgramService $programService,
        RowImporter $rowImporter,
        ImporterEmailDispatcher $emailDispatcher
    ) {
        $this->svc              = $svc;
        $this->parser           = $parser;
        $this->validator        = $validator;
        $this->conflictDetector = $conflictDetector;
        $this->programService   = $programService;
        $this->rowImporter      = $rowImporter;
        $this->emailDispatcher  = $emailDispatcher;
    }

    /**
     * @param int|null $orgId Fallback org from the Step-3 dropdown. Used only for rows
     *                        whose program can't self-resolve to an existing cycle.
     * @param array<int>|null $reachableOrgIds Org IDs the user is allowed to write to
     *                        (Admin = null → no scoping). Used to scope program lookup.
     */
    public function run(ImporterSession $session, int $orgId, bool $acceptedWarnings = false, ?array $reachableOrgIds = null): ImporterResult
    {
        // The controller claims the session atomically (ImporterSessionService::claimForImport).
        if ($session->status !== ImporterSession::STATUS_IMPORTING) {
            $this->svc->transitionTo($session, ImporterSession::STATUS_IMPORTING);
        }

        $fallbackOrg = Organization::findOrFail($orgId);

        // 1. Re-parse from disk (never trust cached rows).
        $absPath = Storage::disk('importer')->path($session->file_path);
        $parsed  = $this->parser->parse($absPath);
        $rows    = $parsed['rows'];

        if (empty($rows)) {
            $this->finalize($session, ImporterSession::STATUS_FAILED);
            return new ImporterResult(0, 0, 0, 0, [], []);
        }

        // 2. Defensive re-validation.
        $mappings = $session->mappings ?? [];
        $valResult = $this->validator->validateRows($rows, $mappings);
        if (!$valResult->isValid()) {
            $this->finalize($session, ImporterSession::STATUS_FAILED);
            return $this->emptyResult($rows, 'Re-validation failed at execute time');
        }

        // 3. Re-detect conflicts (scoped to fallback org for now; conflicts are advisory).
        $warnings = array_map(
            fn($w) => is_array($w) ? $w : $w->toArray(),
            $this->conflictDetector->detect($rows, $mappings, $orgId)
        );

        // 4. Pre-resolve unique programs (per row, may resolve to org other than fallback).
        $programCache = [];
        $orgCache = [$fallbackOrg->id => $fallbackOrg];
        foreach ($rows as $row) {
            $resolved = $this->resolveRow($row, $mappings);
            $key = strtolower(($resolved['program_name'] ?? '') . ':' . ($resolved['program_type'] ?? ''));
            if (!isset($programCache[$key])) {
                $programCache[$key] = $this->programService->findOrCreateForImport($orgId, [
                    'title' => $resolved['program_name'] ?? '',
                    'type'  => $resolved['program_type'] ?? '',
                    'start' => $this->mdyToYmd($resolved['program_start_date'] ?? ''),
                    'end'   => $this->mdyToYmd($resolved['program_end_date']   ?? ''),
                ], $reachableOrgIds);
            }
        }

        // 5. Per-row import.
        $imported = 0; $skipped = 0; $failed = 0;
        $failedRows = []; $skippedRows = [];

        $dispatchPayloads = [];

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2; // header is row 1
            $resolved = $this->resolveRow($row, $mappings);
            $programKey = strtolower(($resolved['program_name'] ?? '') . ':' . ($resolved['program_type'] ?? ''));
            $cycle = $programCache[$programKey] ?? null;
            if (!$cycle) {
                $failed++;
                $failedRows[] = $this->reason($rowNumber, 'No cycle resolved for row');
                continue;
            }

            $rowOrgId = (int) $cycle->organization_id;
            $rowOrg = $orgCache[$rowOrgId] ?? ($orgCache[$rowOrgId] = Organization::findOrFail($rowOrgId));

            try {
                $result = DB::transaction(function () use ($resolved, $rowNumber, $cycle, $rowOrg, $warnings, $acceptedWarnings) {
                    return $this->rowImporter->import($resolved, $rowNumber, $cycle, $rowOrg, $warnings, $acceptedWarnings);
                });
            } catch (\Throwable $e) {
                Log::error('Importer row transaction failed', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
                $failedRows[] = $this->reason($rowNumber, 'Row transaction failed: ' . $e->getMessage());
                continue;
            }

            if (!$result['imported']) {
                $skipped++;
                $skippedRows[] = $this->reason($rowNumber, 'Skipped per accepted warning: ' . ($result['reason'] ?? 'unknown'));
                continue;
            }

            // Queue email dispatch OUTSIDE the transaction (after loop).
            $dispatchPayloads[] = [
                'row'         => $rowNumber,
                'was_created' => $result['was_created'],
                'user'        => $result['user'],
                'cycle'       => $cycle,
                'org'         => $rowOrg,
                'password'    => $result['plain_password'],
            ];
            $imported++;
        }

        // 6. Dispatch mail outside any DB transaction. A row whose email failed was still
        // imported, so it is counted apart from failed rows (it used to be counted twice).
        $emailFailed = 0; $emailFailedRows = [];
        foreach ($dispatchPayloads as $p) {
            $mail = $this->emailDispatcher->dispatch(
                $p['was_created'],
                $p['user'],
                $p['cycle'],
                $p['org'],
                $p['password']
            );
            if ($mail['status'] !== 'sent') {
                $emailFailedRows[] = $this->reason(
                    $p['row'],
                    'Email failed: ' . $mail['reason'],
                    'فشل إرسال البريد: ' . $mail['reason']
                );
                $emailFailed++;
            }
        }

        $this->finalize($session, ImporterSession::STATUS_COMPLETED);

        return new ImporterResult(
            count($rows),
            $imported,
            $skipped,
            $failed,
            $failedRows,
            $skippedRows,
            $emailFailed,
            $emailFailedRows
        );
    }

    private function resolveRow(array $row, array $mappings): array
    {
        $out = [];
        foreach ($mappings as $k => $idx) {
            if ($idx === null) continue;
            $out[$k] = $row[$idx] ?? '';
        }
        // Normalise enum-field casing ('student' → 'Student') so RowImporter and
        // downstream services always see the canonical value.
        return $this->validator->canonicaliseRow($out);
    }

    private function mdyToYmd(?string $s): ?string
    {
        if (!$s) return null;
        if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m)) return null;
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[1], (int) $m[2]);
    }

    private function reason(int $row, string $en, ?string $ar = null): array
    {
        return ['row' => $row, 'reason_en' => $en, 'reason_ar' => $ar ?? $en];
    }

    private function emptyResult(array $rows, string $reason): ImporterResult
    {
        return new ImporterResult(
            count($rows),
            0,
            0,
            count($rows),
            [$this->reason(0, $reason)],
            []
        );
    }

    private function finalize(ImporterSession $session, string $terminal): void
    {
        try {
            $this->svc->transitionTo($session, $terminal);
        } catch (\Throwable $e) {
            // Best-effort terminal transition; log but don't mask the result.
            Log::warning('Importer finalize transitionTo failed', ['e' => $e->getMessage()]);
        }
        try {
            $this->svc->cleanupFile($session);
        } catch (\Throwable $e) {
            Log::warning('Importer file cleanup failed', ['e' => $e->getMessage()]);
        }
    }
}
