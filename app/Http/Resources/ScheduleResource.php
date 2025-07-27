<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'type' => $this->type,
            'days' => $this->days,
            'timeFrom' => $this->tfrom,
            'timeTo' => $this->tto,
            'notes' => $this->notes,
            'active' => (bool) $this->active,
        ];
    }
}
