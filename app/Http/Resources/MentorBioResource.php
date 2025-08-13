<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorBioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'bio'                            => $this->bio_en,
            'specialization'                 => $this->specialization_en,
            'linkedin'                        => $this->linkedin,
            'entrepreneurialExperience'       => (bool) $this->entrepreneurial_experience,
            'entrepreneurialExperienceBrief'  => $this->entrepreneurial_experience_brief,
            'profilePrivacy'                  => (bool) $this->profile_privacy,
            'mentorshipApproach'               => json_decode($this->mentorship_approach, true),
            'expertise_ids'                    => $this->user->mentorExpertises()->pluck('expertise_id')->toArray(),
        ];
    }
}
