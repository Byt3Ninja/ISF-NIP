<?php

namespace App\Services;

use App\Company;
use App\Cycle;
use App\CyclesMentors;
use App\MentorshipRequest;
use App\Messenger;
use App\Organization;
use App\Services\ProtectedFiles\FileAccess;
use App\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Who may start a Messenger conversation with whom (H9).
 *
 * The single owner of the recipient lists the compose screen offers
 * (MessengerController@sendmessage) and of the check every send endpoint
 * applies, so the UI and the server cannot drift apart:
 *  - admin: anyone;
 *  - organization manager / sub-admin: startups and users of their
 *    organizations' programs, and every mentor;
 *  - mentor: startups of the programs they mentor, and founders who asked
 *    them for mentorship;
 *  - judge: startups they are assigned to evaluate;
 *  - founder of an approved startup: other startups in the same program and
 *    that program's mentors;
 *  - anyone may answer a person who has already written to them.
 */
class MessagingRecipients
{
    public function __construct(protected FileAccess $access)
    {
    }

    /** Programs whose startups the sender may message (and filter the compose list by). */
    public function cycleIds(User $sender): array
    {
        if ($sender->hasRole('admin')) {
            return Cycle::pluck('id')->map('intval')->all();
        }
        if ($this->access->isStaff($sender)) {
            return Cycle::whereIn('organization_id', $sender->organizations_ids)->pluck('id')->map('intval')->all();
        }
        if ($sender->hasRole('mentor')) {
            return CyclesMentors::where('user_id', $sender->id)->pluck('cycle_id')->map('intval')->all();
        }
        if ($company = $this->approvedCompany($sender)) {
            return $company->cycle ? [(int) $company->cycle] : [];
        }

        return [];
    }

    /** Startups whose founders the sender may message. */
    public function companiesQuery(User $sender): Builder
    {
        if ($sender->hasRole('admin')) {
            return Company::query();
        }
        if ($company = $this->approvedCompany($sender)) {
            return Company::where('cycle', $company->cycle)->where('id', '!=', $company->id);
        }

        return Company::enrolledInAny($this->cycleIds($sender));
    }

    /** Mentors the sender may message, or null when the compose screen offers none. */
    public function mentorsQuery(User $sender): ?Builder
    {
        if ($sender->hasRole('admin') || $this->access->isStaff($sender)) {
            return User::whereHas('roles', fn ($q) => $q->where('default_for', 'mentor'));
        }
        if ($company = $this->approvedCompany($sender)) {
            return User::whereIn('id', CyclesMentors::where('cycle_id', $company->cycle)->select('user_id'));
        }

        return null;
    }

    /** Organizations whose members the sender may broadcast to. */
    public function organizationIds(User $sender): array
    {
        if ($sender->hasRole('admin')) {
            return Organization::pluck('id')->map('intval')->all();
        }

        return $this->access->isStaff($sender) ? array_map('intval', $sender->organizations_ids) : [];
    }

    public function canMessage(User $sender, ?User $receiver): bool
    {
        if (!$receiver || (int) $receiver->id === (int) $sender->id) {
            return false;
        }
        if ($sender->hasRole('admin')) {
            return true;
        }
        // Answering someone who already wrote to the sender is always allowed.
        if (Messenger::where('sender', $receiver->id)->where('receiver', $sender->id)->exists()) {
            return true;
        }

        if ($receiver->company_id) {
            $companyId = (int) $receiver->company_id;
            if ($this->companiesQuery($sender)->whereKey($companyId)->exists()
                || $this->access->isAssignedJudge($sender, $companyId)
                || ($sender->hasRole('mentor') && MentorshipRequest::where('mentor_id', $sender->id)
                    ->whereIn('user_id', User::where('company_id', $companyId)->select('id'))->exists())) {
                return true;
            }
        }

        if (($mentors = $this->mentorsQuery($sender)) && (clone $mentors)->whereKey($receiver->id)->exists()) {
            return true;
        }

        // Staff may reach the other members of their own organizations.
        return $this->access->isStaff($sender)
            && array_intersect(array_map('intval', $receiver->organizations_ids), $this->organizationIds($sender)) !== [];
    }

    private function approvedCompany(User $sender): ?Company
    {
        $company = $sender->company;

        return $company && $company->approved ? $company : null;
    }
}
