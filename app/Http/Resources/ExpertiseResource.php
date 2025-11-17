<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpertiseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = app()->getLocale();
        $title = $this->title;

        return [
            'id' => $this->id,
            'title' => $title,
            'name' => $title,
            'description' => $this->description,
            'specializations' => $this->children ? $this->children->count() : 0,
            'status' => $this->status
        ];
    }
}
