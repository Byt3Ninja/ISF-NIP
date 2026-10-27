<?php

namespace App\Repositories;

use App\Enums\MentorshipApproval;
use App\MentorshipRequest;
use Illuminate\Database\Eloquent\Collection;

class MentorshipRequestRepository
{
    /**
     * Get all approved mentorship requests that are not completed, in programs
     * that have mentorship reminders switched on.
     *
     * @return Collection
     */
    public function getPendingApprovedRequests(): Collection
    {
        return MentorshipRequest::with([
            'user.company',
            'mentor',
            'cycle.mentorshipSchedules' => function ($query) {
                $query->where('is_active', 1);
            },
            'organization'
        ])
            ->where('approved', MentorshipApproval::APPROVED->value)
            ->whereHas('cycle', function ($query) {
                $query->where('is_active_Rminder', 1);
            })
            ->where(function ($query) {
                $query->whereNull('done')
                      ->orWhere('done', '!=', 1);
            })
            ->get();
    }
}
