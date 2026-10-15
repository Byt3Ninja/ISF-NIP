<?php

namespace App\Services\ProtectedFiles;

use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Answers one question: was this judge ever put on a screening panel for this startup?
 *
 * `screenings.users` holds the startup ids of a judge's panel and
 * `finalscreenings.judges` the judge ids of a startup's final panel; both are
 * JSON arrays kept in text columns, with ids stored as numbers or as strings.
 */
class JudgeAssignments
{
    public function everAssigned(User $judge, ?int $companyId): bool
    {
        if (!$companyId) {
            return false;
        }

        $inScreening = DB::table('screenings')
            ->where('user_id', $judge->id)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($companyId) {
                $q->whereRaw('JSON_VALID(users) AND JSON_CONTAINS(users, ?)', [json_encode($companyId)])
                    ->orWhereRaw('JSON_VALID(users) AND JSON_CONTAINS(users, ?)', [json_encode((string) $companyId)]);
            })
            ->exists();

        if ($inScreening) {
            return true;
        }

        return DB::table('finalscreenings')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($judge) {
                $q->whereRaw('JSON_VALID(judges) AND JSON_CONTAINS(judges, ?)', [json_encode((int) $judge->id)])
                    ->orWhereRaw('JSON_VALID(judges) AND JSON_CONTAINS(judges, ?)', [json_encode((string) $judge->id)]);
            })
            ->exists();
    }

    /** Is the judge on a screening panel of any step of this program? */
    public function judgesProgram(User $judge, ?int $cycleId): bool
    {
        if (!$cycleId) {
            return false;
        }

        $stepIds = DB::table('steps')->where('cycle_id', $cycleId)->pluck('id');
        if ($stepIds->isEmpty()) {
            return false;
        }

        return DB::table('screenings')->where('user_id', $judge->id)->whereNull('deleted_at')->whereIn('step_id', $stepIds)->exists()
            || DB::table('finalscreenings')->whereNull('deleted_at')->whereIn('step_id', $stepIds)
                ->where(function ($q) use ($judge) {
                    $q->whereRaw('JSON_VALID(judges) AND JSON_CONTAINS(judges, ?)', [json_encode((int) $judge->id)])
                        ->orWhereRaw('JSON_VALID(judges) AND JSON_CONTAINS(judges, ?)', [json_encode((string) $judge->id)]);
                })->exists();
    }
}
