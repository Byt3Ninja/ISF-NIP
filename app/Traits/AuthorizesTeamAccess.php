<?php

namespace App\Traits;

use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Team-scoped authorization for hackathon team pages and their sub-resources.
 *
 * The hackathon team endpoints take a team_formation_id straight from the
 * request, so without an explicit ownership check any authenticated founder can
 * read and mutate ANOTHER team's data by changing the id (horizontal privilege
 * escalation), and a member who was removed from a team keeps the access they
 * had while they were on it.
 *
 * Two levels, matching the product rule:
 *   - member  : accepted members collaborate — view the team, upload shared
 *               files, add and manage tasks.
 *   - leader  : only the team leader edits team info / project idea, invites,
 *               and deletes the team.
 *
 * IMPORTANT: membership ALWAYS requires acceptInvition = 'accepted'. Rows for
 * removed / rejected / cancelled / pending members must never grant access.
 */
trait AuthorizesTeamAccess
{
    /**
     * Membership states that still count as "on the team". Anything else
     * (removed, rejected, cancelled, pending, join, failed) does not.
     */
    private function activeMembershipStates(): array
    {
        return ['accepted'];
    }

    /**
     * Is $user an accepted member of $teamFormationId?
     */
    protected function isTeamMember(?User $user, $teamFormationId): bool
    {
        if (!$user || !$teamFormationId) {
            return false;
        }

        return DB::table('team_members')
            ->where('team_formation_id', $teamFormationId)
            ->where('user_id', $user->id)
            ->whereIn('acceptInvition', $this->activeMembershipStates())
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Is $user the accepted leader of $teamFormationId?
     */
    protected function isTeamLeader(?User $user, $teamFormationId): bool
    {
        if (!$user || !$teamFormationId) {
            return false;
        }

        return DB::table('team_members')
            ->where('team_formation_id', $teamFormationId)
            ->where('user_id', $user->id)
            ->where('isTeamLeader', 1)
            ->whereIn('acceptInvition', $this->activeMembershipStates())
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Staff who legitimately oversee the team: full admins, plus org-scoped
     * subadmins / organization managers whose organization owns the team's
     * cycle. Mirrors SharedFileController@canAccessTeam so the rules stay
     * consistent across the hackathon surface.
     */
    protected function isTeamOverseer(?User $user, $teamFormationId): bool
    {
        if (!$user || !$teamFormationId) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasAnyRole(['subadmin', 'organizationManager'])) {
            $organizationId = DB::table('team_formations as tf')
                ->join('cycles as c', 'c.id', '=', 'tf.cycle_id')
                ->where('tf.id', $teamFormationId)
                ->value('c.organization_id');

            return $organizationId && in_array($organizationId, $user->organizations_ids);
        }

        return false;
    }

    /**
     * May $user VIEW / collaborate on the team (accepted member or overseer)?
     */
    protected function canViewTeam(?User $user, $teamFormationId): bool
    {
        return $this->isTeamMember($user, $teamFormationId)
            || $this->isTeamOverseer($user, $teamFormationId);
    }

    /**
     * May $user perform LEADER-only actions (edit team/project, invite, delete)?
     */
    protected function canManageTeam(?User $user, $teamFormationId): bool
    {
        return $this->isTeamLeader($user, $teamFormationId)
            || $this->isTeamOverseer($user, $teamFormationId);
    }
}
