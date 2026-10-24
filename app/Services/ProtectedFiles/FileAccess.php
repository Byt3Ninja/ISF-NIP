<?php

namespace App\Services\ProtectedFiles;

use App\Company;
use App\Cycle;
use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * The one place that decides who may open or change a confidential file.
 * Used by the serving route, by the upload/delete endpoints and by the views
 * (so a link is only shown to someone the route would let through).
 *
 * Rules (platform admins always pass):
 *  - a startup's members (users.company_id) see all of their startup's files;
 *  - organization staff (organizationManager / subadmin) see the files of startups
 *    whose CURRENT program their organization runs, and application files that
 *    were submitted to one of their own programs;
 *  - a judge sees only evaluation material (pitch/plan files, application files)
 *    of startups they were assigned to;
 *  - task files follow the task: its program's staff, its creator, the startups
 *    it is assigned to;
 *  - mentors see nothing here except the files of tasks they created.
 *
 * ownsOrganization() is never the test for a founder: a founder "owns" the
 * organization of their program, which must not open other startups to them.
 */
class FileAccess
{
    public function __construct(protected JudgeAssignments $judges)
    {
    }

    public function canView(?User $user, ProtectedFile $file): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        switch ($file->set) {
            case ProtectedFileSet::COMPANY_LEGAL:
            case ProtectedFileSet::EMPLOYEE_DOCUMENT:
                return $this->isMember($user, $file->company_id)
                    || $this->isCurrentProgramStaff($user, $file->company_id);

            case ProtectedFileSet::COMPANY_PITCH:
                return $this->isMember($user, $file->company_id)
                    || $this->isCurrentProgramStaff($user, $file->company_id)
                    || $this->isAssignedJudge($user, $file->company_id);

            case ProtectedFileSet::FORM_ANSWER:
                // Staff see what was submitted to THEIR program, not whatever the
                // startup's current program happens to be.
                return $this->isMember($user, $file->company_id)
                    || $this->isProgramStaff($user, $file->cycle_id)
                    || $this->isAssignedJudge($user, $file->company_id);

            case ProtectedFileSet::MILESTONE_DOCUMENT:
                if ($this->isMember($user, $file->company_id)) {
                    return true;
                }
                return !$this->isPrivateMilestone($file)
                    && $this->isCurrentProgramStaff($user, $file->company_id);

            case ProtectedFileSet::TASK_ATTACHMENT:
                return $this->canViewTaskFile($user, $file);

            case ProtectedFileSet::PROGRAM_GUIDELINE:
                return $this->isAttachedToProgram($user, $file->cycle_id);
        }

        return false; // unknown set: fail closed
    }

    /** May this user upload, replace or delete the startup's own files? */
    public function canManageCompany(?User $user, ?int $companyId): bool
    {
        if (!$user || !$companyId) {
            return false;
        }

        return $user->hasRole('admin')
            || $this->isMember($user, $companyId)
            || $this->isCurrentProgramStaff($user, $companyId);
    }

    /**
     * May this user open the startup's profile page? (H3) Anyone who may manage it, plus
     * the people the startup works with: judges assigned to it, mentors of its program
     * or of one of its founders' mentorship requests, and service providers that one of
     * its founders requested a service from. Mentors have no organization scope, so they
     * are matched by relationship, never by organization.
     */
    public function canViewCompanyProfile(?User $user, ?int $companyId): bool
    {
        if (!$user || !$companyId) {
            return false;
        }

        return $this->canManageCompany($user, $companyId)
            || $this->isAssignedJudge($user, $companyId)
            || $this->isStartupMentor($user, $companyId)
            || $this->isStartupServiceProvider($user, $companyId);
    }

    /** Would this user be allowed to open files of the given set for this startup? (for showing/hiding links) */
    public function canViewCompanySet(?User $user, ?int $companyId, string $set): bool
    {
        if (!$user || !$companyId) {
            return false;
        }
        if ($user->hasRole('admin') || $this->isMember($user, $companyId) || $this->isCurrentProgramStaff($user, $companyId)) {
            return true;
        }

        return in_array($set, ProtectedFileSet::JUDGE_VISIBLE, true) && $this->isAssignedJudge($user, $companyId);
    }

    // ------------------------------------------------------------------ building blocks

    public function isMember(User $user, ?int $companyId): bool
    {
        return $companyId && $user->company_id && (int) $user->company_id === (int) $companyId;
    }

    public function isStaff(User $user): bool
    {
        return $user->hasAnyRole(['organizationManager', 'subadmin']);
    }

    /** Staff of the organization that runs the startup's current program. */
    public function isCurrentProgramStaff(User $user, ?int $companyId): bool
    {
        if (!$companyId || !$this->isStaff($user)) {
            return false;
        }
        $cycleId = Company::whereKey($companyId)->value('cycle');

        return $this->ownsProgram($user, $cycleId ? (int) $cycleId : null);
    }

    /** Staff of the organization that runs this program. */
    public function isProgramStaff(User $user, ?int $cycleId): bool
    {
        return $this->isStaff($user) && $this->ownsProgram($user, $cycleId);
    }

    public function isAssignedJudge(User $user, ?int $companyId): bool
    {
        return $user->hasRole('judger') && $this->judges->everAssigned($user, $companyId);
    }

    /** A mentor of the startup's program, or the mentor of a request one of its founders made. */
    public function isStartupMentor(User $user, ?int $companyId): bool
    {
        if (!$companyId || !$user->hasRole('mentor')) {
            return false;
        }

        $cycleId = Company::whereKey($companyId)->value('cycle');
        if ($cycleId && DB::table('cycles_mentors')->where('user_id', $user->id)->where('cycle_id', $cycleId)->whereNull('deleted_at')->exists()) {
            return true;
        }

        return DB::table('mentorship_requests')
            ->where('mentor_id', $user->id)
            ->whereNull('deleted_at')
            ->whereIn('user_id', User::where('company_id', $companyId)->select('id'))
            ->exists();
    }

    /** A service provider owning a service that one of the startup's founders requested. */
    public function isStartupServiceProvider(User $user, ?int $companyId): bool
    {
        if (!$companyId || !$user->hasRole('serviceProvider')) {
            return false;
        }

        $providerId = $user->service_provider?->id;
        if (!$providerId) {
            return false;
        }

        return DB::table('services_requests')
            ->join('services', 'services.id', '=', 'services_requests.service_id')
            ->where('services.service_provider_id', $providerId)
            ->whereNull('services_requests.deleted_at')
            ->whereIn('services_requests.user_id', User::where('company_id', $companyId)->select('id'))
            ->exists();
    }

    protected function ownsProgram(User $user, ?int $cycleId): bool
    {
        if (!$cycleId) {
            return false;
        }

        $organizationId = Cycle::whereKey($cycleId)->value('organization_id');
        if (!$organizationId) {
            return false;
        }

        // Linked through users.organization_id / the sub-admin pivot, or recorded as the
        // organization's owner (organizations.user_id), which the company screens also honour.
        return $user->ownsOrganization($organizationId)
            || DB::table('organizations')->where('id', $organizationId)->where('user_id', $user->id)->exists();
    }

    protected function isPrivateMilestone(ProtectedFile $file): bool
    {
        if ($file->owner_type !== 'milestone') {
            return false;
        }

        return DB::table('milestones')->where('id', $file->owner_id)->value('visibility') === 'private';
    }

    /**
     * Task files. `owner_type` is `task` (attachments and feedback on the task
     * itself, plus submissions stored at task level) or `task_assignee` (a
     * startup's own submission row). A submission is visible to the startup that
     * made it; an entry that should name a startup but does not is refused.
     */
    protected function canViewTaskFile(User $user, ProtectedFile $file): bool
    {
        $taskId = $file->owner_type === 'task_assignee'
            ? DB::table('program_task_assignees')->where('id', $file->owner_id)->value('program_task_id')
            : $file->owner_id;

        $task = $taskId ? DB::table('program_tasks')->where('id', $taskId)->first(['id', 'program_id', 'created_by']) : null;
        if (!$task) {
            return false;
        }

        if ((int) $task->created_by === (int) $user->id || $this->isProgramStaff($user, $task->program_id ? (int) $task->program_id : null)) {
            return true;
        }

        if (!$user->company_id) {
            return false;
        }

        $isSubmission = $file->owner_type === 'task_assignee' || $file->field === 'submission';
        if ($isSubmission) {
            return $this->isMember($user, $file->company_id);
        }

        // Attachments and feedback on the task: any startup the task is assigned to.
        return DB::table('program_task_assignees')
            ->where('program_task_id', $task->id)
            ->where('company_id', $user->company_id)
            ->exists();
    }

    /** Program guideline documents: the people working in or on that program. */
    protected function isAttachedToProgram(User $user, ?int $cycleId): bool
    {
        if (!$cycleId) {
            return false;
        }
        if ($this->isProgramStaff($user, $cycleId)) {
            return true;
        }
        if ($user->hasRole('judger') && $this->judges->judgesProgram($user, $cycleId)) {
            return true;
        }
        if ($user->hasRole('mentor') && DB::table('cycles_mentors')->where('cycle_id', $cycleId)->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $user->company_id
            && (int) Company::whereKey($user->company_id)->value('cycle') === (int) $cycleId;
    }
}
