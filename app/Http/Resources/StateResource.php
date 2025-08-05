<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $name = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return [
            'id' => $this->id,
            'title' => $name,
        ];
    }
}
