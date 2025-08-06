<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorshipSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $locale = app()->getLocale();
         $mentor = optional($this->mentor);
         $bio = optional($mentor->bio);
         return [
            'sessionId' => $this->id,
            'mentorId' => $this->mentor_id,
            'mentorName' => optional($this->mentor)->first_name_en . ' ' . optional($this->mentor)->last_name_en,
            'mentorImage' => $mentor && $mentor->img_url ? Storage::disk('public_uploads')->url($mentor->img_url): null,
            'bio' => $bio->{"bio_$locale"},
            'linkedin' => $bio->linkedin,
            'specialization' => $bio->{"specialization_$locale"},
            'mentorExpertises' => $mentor->mentorExpertises->map(function ($exp) use ($locale) {
            return $exp->{"title" . ($locale === 'ar' ? '_ar' : '')};
            }),
            'sessionDateTime' => optional($this->zoom_date)->toDateTimeString(),
        ];
    }
}
