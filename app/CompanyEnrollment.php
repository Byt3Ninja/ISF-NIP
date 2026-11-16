<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * One row for each time a company (an "idea" on ISF) enters a cycle: the step it
 * reached and how it ended.
 *
 * `companies.cycle` / `companies.step` only say where a company is now, and both are
 * blanked when it is rejected or marked as alumni, so on their own they keep no
 * history. Company's `saved` hook mirrors every change of that pointer into these
 * rows (see syncFromPointer). The two places that end an enrollment for a known
 * reason name it first with closeActive(), and the one bulk step update, which
 * fires no model events, calls moveToStep().
 *
 * A company may have several rows for the same cycle: leaving and joining again adds
 * a row instead of rewriting the earlier one.
 */
class CompanyEnrollment extends Model
{
    public const STATUS_ACTIVE = 'active';

    /** Marked as alumni of the cycle. */
    public const STATUS_COMPLETED = 'completed';

    /** Disapproved at screening. */
    public const STATUS_REJECTED = 'rejected';

    /** Left the cycle some other way, for example moved to another cycle by an import. */
    public const STATUS_WITHDRAWN = 'withdrawn';

    // company_id and cycle_id are owner foreign keys: never mass-assignable, set them explicitly.
    protected $fillable = ['step_id', 'status', 'approved', 'approved_by', 'joined_at', 'ended_at'];

    protected $casts = [
        'company_id' => 'integer',
        'cycle_id' => 'integer',
        'step_id' => 'integer',
        'approved' => 'integer',
        'approved_by' => 'integer',
        'joined_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id');
    }

    /**
     * The values `companies` holds while this enrollment is the selected one.
     *
     * @return array{cycle: int, step: int|null, approved: int|null, approved_by: int|null}
     */
    public function pointerValues(): array
    {
        return [
            'cycle' => $this->cycle_id,
            'step' => $this->step_id,
            'approved' => $this->approved,
            'approved_by' => $this->approved_by,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Mirror a change of the company's cycle / step pointer.
     *
     * The pointer holds one cycle, so pointing it somewhere else means the company has
     * left the previous one as far as every current screen is concerned.
     *
     * @param  int|string|null  $previousCycle  the cycle the pointer held before this save
     */
    public static function syncFromPointer(Company $company, $previousCycle): void
    {
        $cycle = $company->cycle ? (int) $company->cycle : null;
        $previous = $previousCycle ? (int) $previousCycle : null;

        if ($previous && $previous !== $cycle) {
            self::closeActive($company->id, $previous, self::STATUS_WITHDRAWN);
        }

        if (! $cycle) {
            return;
        }

        $step = $company->step ? (int) $company->step : null;
        $enrollment = self::query()->active()
            ->where('company_id', $company->id)
            ->where('cycle_id', $cycle)
            ->latest('id')
            ->first();

        if ($enrollment) {
            if ($enrollment->step_id !== $step) {
                $enrollment->update(['step_id' => $step]);
            }

            return;
        }

        $enrollment = new self(['step_id' => $step, 'status' => self::STATUS_ACTIVE, 'joined_at' => now()]);
        $enrollment->company_id = $company->id;
        $enrollment->cycle_id = $cycle;
        $enrollment->save();
    }

    /**
     * End the company's active enrollment in a cycle with the given outcome. Does
     * nothing when there is none, so it is safe to call before the pointer is cleared.
     *
     * @param  int|string|null  $cycleId
     */
    public static function closeActive(int $companyId, $cycleId, string $status): void
    {
        if (! $cycleId) {
            return;
        }

        self::query()->active()
            ->where('company_id', $companyId)
            ->where('cycle_id', (int) $cycleId)
            ->update(['status' => $status, 'ended_at' => now()]);
    }

    /**
     * Follow a bulk `Company::whereIn(...)->update(['step' => ...])`. Only companies
     * active in the step's own cycle are moved.
     *
     * @param  array<int, int|string>  $companyIds
     */
    public static function moveToStep(array $companyIds, int $stepId): void
    {
        $cycleId = Step::query()->whereKey($stepId)->value('cycle_id');

        if (! $cycleId || $companyIds === []) {
            return;
        }

        self::query()->active()
            ->whereIn('company_id', $companyIds)
            ->where('cycle_id', $cycleId)
            ->update(['step_id' => $stepId]);
    }

    /**
     * Build rows for what was recorded before this table existed: where each company
     * is now, and each cycle it was marked as alumni of. Safe to run more than once.
     *
     * `joined_at` stays empty for these rows because the date was never stored.
     * Companies rejected in the past left no trace, so they cannot be rebuilt.
     */
    public static function backfill(): void
    {
        $now = now();

        DB::table('company_enrollments')->insertUsing(
            ['company_id', 'cycle_id', 'step_id', 'status', 'created_at', 'updated_at'],
            DB::table('companies as c')
                ->join('cycles as cy', 'cy.id', '=', 'c.cycle')
                ->leftJoin('steps as s', 's.id', '=', 'c.step')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('company_enrollments as e')
                        ->whereColumn('e.company_id', 'c.id')
                        ->whereColumn('e.cycle_id', 'c.cycle')
                        ->where('e.status', self::STATUS_ACTIVE);
                })
                ->selectRaw('c.id, c.cycle, s.id, ?, ?, ?', [self::STATUS_ACTIVE, $now, $now])
        );

        DB::table('company_enrollments')->insertUsing(
            ['company_id', 'cycle_id', 'status', 'ended_at', 'created_at', 'updated_at'],
            DB::table('alumni as a')
                ->join('companies as c', 'c.id', '=', 'a.company_id')
                ->join('cycles as cy', 'cy.id', '=', 'a.cycle_id')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('company_enrollments as e')
                        ->whereColumn('e.company_id', 'a.company_id')
                        ->whereColumn('e.cycle_id', 'a.cycle_id')
                        ->where('e.status', self::STATUS_COMPLETED);
                })
                ->groupBy('a.company_id', 'a.cycle_id')
                ->selectRaw(
                    'a.company_id, a.cycle_id, ?, MAX(COALESCE(a.created_at, a.established_date)), ?, ?',
                    [self::STATUS_COMPLETED, $now, $now]
                )
        );
    }
}
