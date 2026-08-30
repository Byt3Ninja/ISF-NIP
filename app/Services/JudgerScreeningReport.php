<?php

namespace App\Services;

use App\Company;
use App\Cycle;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\ScreeningEvaluationResults;
use App\Team_member;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Builds the judge-scoped screening report consumed by both the PDF and the
 * Excel export. (GV-3340)
 *
 * The criteria hierarchy belongs to a step's evaluation form, so a selection
 * spanning several steps cannot be one flat table: the report is grouped by
 * step, each group carrying its own criteria tree plus one row per startup
 * holding ONLY the current judge's answers.
 */
class JudgerScreeningReport
{
    /** @var int */
    protected $judgeId;

    public function __construct( $judgeId )
    {
        $this->judgeId = intval( $judgeId );
    }

    /**
     * @param Collection $companies Rows shaped by JudgerScreeningController::assignedCompanies()
     * @return Collection of step groups: { step, program, is_hackathon, criteria, rows }
     */
    public function build( Collection $companies ): Collection
    {
        return $companies->groupBy(function( $company ) {
            return $company->step->id;
        })->map(function( $group ) {
            return $this->buildStepGroup( $group );
        })->filter()->values();
    }

    protected function buildStepGroup( Collection $group )
    {
        $first = $group->first();
        $step_id = $first->step->id;

        $companyIds = $group->pluck('id')->map(fn( $i ) => intval( $i ))->unique()->values()->toArray();
        $results = ScreeningEvaluationResults::results( $step_id, $companyIds );

        // Criteria tree: take it from the evaluation form itself so the columns
        // stay identical for every startup in the step, including startups this
        // judge has not evaluated yet (they still need their empty row).
        $criteria = collect();
        foreach( $companyIds as $company_id )
        {
            $companyCriteria = $results[$company_id]['criteria'] ?? null;
            if( !empty( $companyCriteria ) && count( $companyCriteria ) > 0 ) { $criteria = collect( $companyCriteria ); break; }
        }
        if( $criteria->isEmpty() ) { return null; }

        $isHackathon = strtolower( (string) ( $first->program->type ?? '' ) ) === 'hackathon';

        $teamNames = $isHackathon ? $this->teamNames( $companyIds ) : collect();
        $founders = $this->founderNames( $companyIds );
        $timestamps = $this->timestamps( $step_id, $companyIds );

        $rows = $group->map(function( $company ) use ( $results, $criteria, $isHackathon, $teamNames, $founders, $timestamps ) {
            return $this->buildRow( $company, $results, $criteria, $isHackathon, $teamNames, $founders, $timestamps );
        })->values();

        $out = new stdClass;
        $out->step = $first->step;
        $out->program = $first->program;
        $out->is_hackathon = $isHackathon;
        $out->criteria = $criteria;
        $out->rows = $rows;

        return $out;
    }

    /**
     * One startup row: the startup columns, this judge's answer for every
     * sub-criterion (comment / score / weighted), the running sub-criteria and
     * criteria totals, and the summary block.
     */
    protected function buildRow( $company, $results, Collection $criteria, $isHackathon, $teamNames, $founders, $timestamps )
    {
        $company_id = intval( $company->id );
        $companyData = $results[$company_id] ?? null;

        $row = new stdClass;
        $row->startup = $company->name;
        $row->team_name = $isHackathon ? ( $teamNames[$company_id] ?? '' ) : null;
        $row->founder = $founders[$company_id] ?? '';
        $row->status = $company->status ?? '';
        $row->criteria = collect();
        $row->comment = '';
        $row->approval = 'pending';
        $row->total = 0;
        $row->timestamp = $timestamps[$company_id] ?? null;

        // This judge's evaluation of this startup, if any.
        $judge = null;
        if( $companyData )
        {
            $judges = collect( $companyData['judges'] ?? [] );
            $judge = $judges->first(function( $j ) {
                return intval( $j->id ?? 0 ) === $this->judgeId;
            });
        }

        $judgeCriteria = $judge && !empty( $judge->evaluation ) ? collect( $judge->evaluation->details ?? [] ) : collect();

        $row->criteria = $criteria->map(function( $criterion ) use ( $judgeCriteria ) {
            $judgeCriterion = $judgeCriteria->first(function( $c ) use ( $criterion ) {
                return intval( $c->id ?? 0 ) === intval( $criterion->id );
            });
            $judgeSubs = $judgeCriterion ? collect( $judgeCriterion->details ?? [] ) : collect();

            $out = new stdClass;
            $out->id = $criterion->id;
            $out->name = $criterion->name;
            $out->details = collect( $criterion->details ?? [] )->map(function( $sub ) use ( $judgeSubs ) {
                $answer = $judgeSubs->first(function( $s ) use ( $sub ) {
                    return intval( $s->id ?? 0 ) === intval( $sub->id );
                });

                $detail = new stdClass;
                $detail->id = $sub->id;
                $detail->name = $sub->name;
                $detail->weight = $sub->weight ?? 0;
                $detail->answered = !empty( $answer );
                $detail->comment = $answer->comment ?? '';
                $detail->score = $answer ? $answer->score : null;
                // Weighted contribution of this sub-criterion, already computed
                // by results() as (score / max) * weight.
                $detail->weighted = $answer ? round( floatval( $answer->value ), 2 ) : null;
                $detail->total = $detail->weighted;
                return $detail;
            })->values();

            // Criteria total = this judge's weighted total for the criterion.
            $out->total = $judgeCriterion ? round( floatval( $judgeCriterion->value ), 2 ) : null;
            return $out;
        })->values();

        if( $judge && !empty( $judge->evaluation ) )
        {
            $row->comment = $judge->evaluation->comment ?? '';
            $row->approval = $judge->evaluation->approval ?? 'pending';
            $row->total = round( floatval( $judge->evaluation->value ?? 0 ), 2 );
        }

        return $row;
    }

    /**
     * Hackathon team name per company. team_formations carries no company_id;
     * the link runs through the team member rows.
     */
    protected function teamNames( array $companyIds ): Collection
    {
        return Team_member::whereIn('Company_id', $companyIds)
            ->whereNotNull('team_formation_id')
            ->with('teamFormation')
            ->get()
            ->groupBy('Company_id')
            ->map(function( $members ) {
                $member = $members->first(function( $m ) { return !empty( $m->teamFormation ); });
                return $member ? ( $member->teamFormation->name ?? '' ) : '';
            });
    }

    /**
     * Founder name per company: the startup's registered (founder) users.
     */
    protected function founderNames( array $companyIds ): Collection
    {
        return Company::whereIn('id', $companyIds)
            ->with('users')
            ->get()
            ->mapWithKeys(function( $company ) {
                $names = $company->users->map(function( $u ) { return trim( (string) $u->name ); })
                    ->filter()->unique()->values();
                $founder = $names->isNotEmpty() ? $names->implode(', ') : trim( (string) ( $company->founder ?? '' ) );
                return [ intval( $company->id ) => $founder ];
            });
    }

    /**
     * When this judge last saved their evaluation for each startup, taken from
     * the final-comment value row that every publish/save writes.
     */
    protected function timestamps( $step_id, array $companyIds ): Collection
    {
        $evaluation = ScreeningEvaluation::getEvaluationData( $step_id );
        if( empty( $evaluation ) || empty( $evaluation->id ) ) { return collect(); }

        $fieldIds = ScreeningEvaluationCustomFields::where('evaluation_id', $evaluation->id)->pluck('id')->toArray();
        if( empty( $fieldIds ) ) { return collect(); }

        return ScreeningEvaluationFieldsValues::whereIn('custom_field_id', $fieldIds)
            ->where('user_id', $this->judgeId)
            ->whereIn('company_id', $companyIds)
            ->get()
            ->groupBy('company_id')
            ->map(function( $values ) {
                return $values->max('updated_at');
            });
    }

    /**
     * Recommendation label for the summary column.
     */
    public static function recommendationLabel( $approval ): string
    {
        if( $approval === 'approved' ) { return __('admin.Approve'); }
        if( $approval === 'disapproved' ) { return __('admin.Disapprove'); }
        return __('admin.Didn\'t decide yet');
    }
}
