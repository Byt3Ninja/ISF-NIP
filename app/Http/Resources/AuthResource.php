<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        return [
            'id'        => $this->id,
            'firstName' => $this->{'first_name_' . $locale},
            'lastName'  => $this->{'last_name_' . $locale},
            'email'     => $this->email,
            'phone'     => $this->country_code . $this->phone,
            'imgUrl'    => $this->img_url ? Storage::disk('public_uploads')->url($this->img_url) : null,
            'role'      => $this->getRoleNames()->first(),
        ];
    }
}
