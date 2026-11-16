<?php

namespace App\Console\Commands;

use App\{Company, CompanyEnrollment, TeamFormation};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\TimezoneService;
use Carbon\Carbon;
use stdClass;

class changeCycleStep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycle:change-step';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to change the current cycle step based on the current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i:s');
        $this->info("Starting Command: " . $this->signature . " At: " . $now);
        $cycles = DB::table('cycles AS c')
            ->select([
                'c.id',
                'c.organization_id',
                'c.start',
                'c.end',
                's.id AS step_id',
                's.arr AS step_arr',
                's.from AS step_from',
                's.to AS step_to',
            ])
            ->leftJoin('steps AS s', 'c.id', '=', 's.cycle_id')
            ->where('c.step_type', 'time')
            ->whereNull('c.deleted_at')
            ->get();

        $cycles = $cycles->groupBy('id')->map(function ($groupedCycle) {
            $c = $groupedCycle->first();
            // GV-9566: step dates are days in the program's platform zone, so "now"
            // is that zone's wall-clock, not the server clock (hourly cron).
            $now = TimezoneService::platformNow(TimezoneService::platform($c->organization_id ? (int) $c->organization_id : null))->format('Y-m-d H:i:s');
            $cycle = new stdClass;
            $cycle->id = $c->id;
            $cycle->start = Carbon::parse($c->start)->format('Y-m-d 00:00:00');
            $cycle->end = Carbon::parse($c->end)->format('Y-m-d 23:59:59');
            $cycle->steps = $groupedCycle->map(function ($step) {
                $s = new stdClass;
                $s->id = $step->step_id;
                $s->arr = $step->step_arr;
                $s->from = Carbon::parse($step->step_from)->format('Y-m-d 00:00:00');
                $s->to = Carbon::parse($step->step_to)->format('Y-m-d 23:59:59');
                return $s;
            })->sortBy('from')->values();
            $cycle->eligible_steps = $cycle->steps->filter(function( $step ) use ( $now ) {
                return $now >= $step->from && $now <= $step->to;
            });
            return $cycle;
        })->values();
        $cycles_ids = $cycles->pluck('id');

        // One row per active enrollment: a company in two time-driven cycles is moved in each.
        $companies = DB::table('company_enrollments AS ce')
            ->join('companies AS c', 'c.id', '=', 'ce.company_id')
            ->select([
                'c.id',
                'ce.cycle_id AS cycle',
                'ce.step_id AS step',
            ])
            ->whereNull('c.deleted_at')
            ->where('ce.status', CompanyEnrollment::STATUS_ACTIVE)
            ->whereIn('ce.cycle_id', $cycles_ids)
            ->get();

        $teams = DB::table('team_formations AS tf')
            ->select([
                'tf.id',
                'tf.cycle_id',
                'tf.step_id',
            ])
            ->whereIn('tf.cycle_id', $cycles_ids)
            ->get();

        $companiesToUpdate = []; $teamsToUpdate = [];
        foreach( $companies as $company )
        {
            $cycle = $cycles->where('id', $company->cycle)->first();
            if( !empty( $cycle ) )
            {
                $eligible_step = $cycle->eligible_steps;
                if( !$eligible_step->isEmpty() && !in_array( $company->step, $eligible_step->pluck('id')->toArray() ) )
                {
                    $companiesToUpdate[] = [
                        'id' => $company->id,
                        'step' => $eligible_step->last()->id
                    ];
                }
                
            }
        }

        foreach( $teams as $team )
        {
            $cycle = $cycles->where('id', $team->cycle_id)->first();
            if( !empty( $cycle ) )
            {
                $eligible_step = $cycle->eligible_steps;
                if( !$eligible_step->isEmpty() && !in_array( $team->step_id, $eligible_step->pluck('id')->toArray() ) )
                {
                    $teamsToUpdate[] = [
                        'id' => $team->id,
                        'step' => $eligible_step->last()->id
                    ];
                }
            }
        }
        $teamsToUpdate = collect($teamsToUpdate);
        $companiesToUpdate = collect($companiesToUpdate);
        if( !$companiesToUpdate->isEmpty() )
        {
            $this->info("Changing Step for: " . $companiesToUpdate->count() . " Companies");
            $grouping = $companiesToUpdate->groupBy('step')->map(function( $step ) { return $step->pluck('id')->toArray(); });
            foreach( $grouping as $step_id => $companies_ids )
            {
                // The pointer follows only for companies that have this step's cycle selected.
                $step_cycle_id = $cycles->first(fn ($cycle) => $cycle->steps->contains('id', $step_id))?->id;
                Company::whereIn('id', $companies_ids)->where('cycle', $step_cycle_id)->update(['step' => $step_id]);
                // A query update fires no model events, so the enrollment history is moved here.
                CompanyEnrollment::moveToStep($companies_ids, (int) $step_id);
            }
        }
        if( !$teamsToUpdate->isEmpty() )
        {
            $this->info("Changing Step for: " . $teamsToUpdate->count() . " Teams");
            $grouping = $teamsToUpdate->groupBy('step')->map(function( $step ) { return $step->pluck('id')->toArray(); });
            foreach( $grouping as $step_id => $teams_ids ) { TeamFormation::whereIn('id', $teams_ids)->update(['step_id' => $step_id]); }
        }
    }
}
