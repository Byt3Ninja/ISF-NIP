<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FounderResource extends JsonResource
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
            'name' => $this->Name,
            'email' => $this->Email,
            'phone' => $this->phone,
            'linkedin' => $this->LinkedIn,
            'join_date' => optional($this->join_date)->format('Y-m-d'),
            'founder_type' => $this->founder_type,
            'working_commitment' => $this->working_commitment,
        ];
    }
}
