<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs Final Screening judge assignments corrupted by the old
 * addJudgingPanels() sync, which wrote finalscreenings rows per panel with
 * updateOrCreate keyed on (step_id, company_id). A company that appeared in
 * more than one panel had its judges overwritten by the last panel processed,
 * so every company ended up showing only that final panel's judge on the
 * results/reports pages.
 *
 * The screenings table is the source of truth (one row per judge per panel,
 * with the panel's company IDs in `users`). This migration rebuilds each
 * Final Screening step's finalscreenings rows as the UNION of every panel's
 * judges per company — the same logic the fixed controller now applies.
 *
 * Idempotent: re-running recomputes from screenings and produces the same rows.
 * Only steps that actually have screenings panel data are touched; steps whose
 * assignments came from another flow (e.g. the Finalscreening update() path)
 * have no screenings rows and are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $finalStepIds = DB::table('steps')->where('stype', 5)->pluck('id');

        foreach ($finalStepIds as $stepId) {
            $panels = DB::table('screenings')
                ->where('step_id', $stepId)
                ->whereNull('deleted_at')
                ->get(['user_id', 'users']);

            if ($panels->isEmpty()) {
                // No panel data for this step — nothing authoritative to rebuild from.
                continue;
            }

            // Merge judges per company across all panels.
            $judgesByCompany = [];
            foreach ($panels as $panel) {
                $companyIds = $this->decodeIds($panel->users);
                $judgeId = (int) $panel->user_id;
                foreach ($companyIds as $companyId) {
                    $judgesByCompany[$companyId][] = $judgeId;
                }
            }

            // Rebuild this step's finalscreenings rows (hard delete: the model
            // does not use the SoftDeletes trait, so there is no soft-delete scope).
            DB::table('finalscreenings')->where('step_id', $stepId)->delete();

            foreach ($judgesByCompany as $companyId => $judgeIds) {
                $judgeIds = array_values(array_unique(array_map('intval', $judgeIds)));
                DB::table('finalscreenings')->insert([
                    'step_id'    => $stepId,
                    'company_id' => $companyId,
                    'judges'     => json_encode($judgeIds),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Data backfill — not reversible. Re-running up() reproduces the same rows.
    }

    /**
     * Normalize the screenings.users column (JSON string or array) to an int array.
     */
    private function decodeIds($value): array
    {
        if (is_array($value)) {
            return array_map('intval', $value);
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_map('intval', $decoded) : [];
    }
};
