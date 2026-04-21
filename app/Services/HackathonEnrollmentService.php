<?php

namespace App\Services;

use App\Company;
use App\Cycle;
use App\Step;
use App\TeamFormation;
use App\Team_member;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates writes for hackathon team formations used by the importer.
 *
 * Mirrors the persistence portion of HackathonController@createTeam but is
 * parameterized rather than request-driven so the importer can call it
 * directly. The legacy controller path is intentionally untouched (its flow
 * has additional side-effects — group chat creation, image upload, audit —
 * outside the scope of importer enrollment).
 */
class HackathonEnrollmentService
{
    /**
     * Idempotently enrols $user as the team leader of a TeamFormation
     * scoped to ($cycle, $company). Creates the formation if missing and
     * the founder Team_member row.
     */
    public function enrollAsTeamLeader(Cycle $cycle, User $user, Company $company): TeamFormation
    {
        $teamName = trim((string) ($company->name_en ?? $company->name_ar ?? ('Team #' . $company->id)));

        // Match an existing formation by (cycle, leader user_id). We do NOT match
        // by company alone because team_formations does not carry company_id.
        $formation = TeamFormation::where('cycle_id', $cycle->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$formation) {
            $stepId = $this->resolveStepId($cycle);

            $formation = new TeamFormation();
            $formation->name        = $this->uniqueName($teamName);
            $formation->description = null;
            $formation->cycle_id    = $cycle->id;
            $formation->step_id     = $stepId;
            $formation->user_id     = $user->id;
            $formation->target_track   = 0;
            $formation->project_status = 0;
            $formation->region         = '';
            $formation->save();
        }

        $existingLeader = Team_member::where('team_formation_id', $formation->id)
            ->where('user_id', $user->id)
            ->where('isTeamLeader', 1)
            ->first();

        if (!$existingLeader) {
            $tm = new Team_member();
            $tm->user_id            = $user->id;
            $tm->team_formation_id  = $formation->id;
            $tm->TeamMember         = 'hackathon';
            $tm->Name               = trim(($user->first_name_en ?? $user->first_name ?? '') . ' ' . ($user->last_name_en ?? $user->last_name ?? ''));
            $tm->Email              = $user->email;
            $tm->acceptInvition     = 'accepted';
            $tm->isTeamLeader       = 1;
            $tm->Company_id         = $company->id;
            $tm->save();
        }

        return $formation->fresh();
    }

    /**
     * Attaches an existing Team_member (a Co-founder) to the leader's
     * TeamFormation as a non-leader member. Idempotent.
     */
    public function enrollAsTeamMember(Cycle $cycle, Team_member $teamMember, Company $company): TeamFormation
    {
        // Locate the leader's formation for this cycle+company
        $leaderTm = Team_member::where('Company_id', $company->id)
            ->whereHas('teamFormation', function ($q) use ($cycle) {
                $q->where('cycle_id', $cycle->id);
            })
            ->where('isTeamLeader', 1)
            ->first();

        if (!$leaderTm || !$leaderTm->team_formation_id) {
            // No leader yet — nothing we can attach to. Caller (RowImporter)
            // is expected to enrol the leader first.
            throw new \RuntimeException('Cannot enrol member before leader exists');
        }

        $formationId = $leaderTm->team_formation_id;

        // Idempotent attach
        if ($teamMember->team_formation_id !== $formationId) {
            $teamMember->team_formation_id = $formationId;
            $teamMember->acceptInvition    = 'accepted';
            $teamMember->isTeamLeader      = 0;
            if (empty($teamMember->Company_id)) {
                $teamMember->Company_id = $company->id;
            }
            $teamMember->save();
        }

        return TeamFormation::findOrFail($formationId);
    }

    /**
     * Pick a step linked to the cycle (preferring the first by id) — falls back
     * to the lowest step id in the table if none are linked.
     */
    private function resolveStepId(Cycle $cycle): int
    {
        $step = DB::table('steps')->where('cycle_id', $cycle->id)->orderBy('id')->first();
        if ($step) {
            return (int) $step->id;
        }
        $any = DB::table('steps')->orderBy('id')->first();
        if ($any) {
            return (int) $any->id;
        }
        throw new \RuntimeException('No steps available to attach hackathon team formation');
    }

    private function uniqueName(string $base): string
    {
        $name = $base;
        $i = 1;
        while (TeamFormation::where('name', $name)->exists()) {
            $name = $base . ' #' . (++$i);
            if ($i > 1000) {
                $name = $base . ' #' . uniqid();
                break;
            }
        }
        return $name;
    }
}
