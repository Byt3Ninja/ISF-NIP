<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
           return [
            'sessionId' => $this->id,
            'mentorId' => $this->mentor_id,
            'mentorName' => optional($this->mentor)->first_name_en . ' ' . optional($this->mentor)->last_name_en,
            'sessionDateTime' => optional($this->zoom_date)->toDateTimeString(),
            'attended' => $this->done ? 'Yes' : 'No',
        ];
    }
}
