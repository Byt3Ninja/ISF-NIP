<?php

namespace App\Http\Resources;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    use ApiResponder;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalStartups = $this->cycles->sum(function ($cycle) {
            return $cycle->companies->count();
        });
        $locale = app()->getLocale();
        $name = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return [
            'id' => $this->id,
            'name' => $name,
            'imgUrl' => $this->img_url ? asset($this->img_url) : null,
            'status' => $this->status,
            'startups' => $totalStartups,
            'cycles' => $this->cycles->count(),
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'countryCode' => $this->user->country_code,
                'gender' => $this->user->gender,
                'imgUrl' => $this->user->img_url ? Storage::disk('public_uploads')->url($this->user->img_url) : null,
            ] : null,
        ];
    }
}
