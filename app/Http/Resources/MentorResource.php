<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class MentorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $locale = app()->getLocale();

        $rating = DB::table('mentorship_requests')->where('mentor_id', $this->id)
            ->whereNotNull('rating')
            ->avg('rating');

        $programs = DB::table('cycles_mentors')
            ->where('user_id', $this->id)
            ->pluck('cycle_id')
            ->unique();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'title' => $this->title_en,
            'imgUrl' => $this->img_url 
                ? Storage::disk('public_uploads')->url($this->img_url) 
                : null,
            'bio' => optional($this->bio)->{"bio_$locale"},
            'linkedin' => optional($this->bio)->linkedin,
            'specialization' => optional($this->bio)->{"specialization_$locale"},
            'mentorExpertises' => $this->mentorExpertises->map(fn ($exp) =>
                $locale === 'ar' ? $exp->title_ar : $exp->title
            ),
            'rating' => round($rating ?? 0, 2),
            'programCount' => $programs->count(),
            'programIds' => $programs->values(),
            'role' => $this->getRoleNames()->first(),
        ];
    }
}
