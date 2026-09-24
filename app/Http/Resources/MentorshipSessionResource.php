<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\TimezoneService;

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
         $avgRating = $mentor->mentorshipRequests()
        ->whereNotNull('rating')
        ->avg('rating');
         return [
            'sessionId' => $this->id,
            'mentor' => [
                'id' => $this->mentor_id,
                'name' => optional($this->mentor)->first_name_en . ' ' . optional($this->mentor)->last_name_en,
                'imgUrl' => $mentor && $mentor->img_url ? Storage::disk('public_uploads')->url($mentor->img_url): null,
                'bio' => $bio->{"bio_$locale"},
                'linkedin' => $bio->linkedin,
                'specialization' => $bio->{"specialization_$locale"},
                'mentorExpertises' => $mentor->mentorExpertises->map(function ($exp) use ($locale) {
                return $exp->{"title" . ($locale === 'ar' ? '_ar' : '')};
            }),
            'rating' => round($avgRating, 1),
            'notes' => $this->comment,
         ],
            // GV-9566: the session instant in the requesting user's own timezone.
            'sessionDateTime' => optional(TimezoneService::forViewer($this->zoom_date, $request->user()))->toDateTimeString(),
        ];
    }
}
