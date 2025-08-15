<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
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
            'firstName'     => $this->first_name_en,
            'lastName'      => $this->last_name_en,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'gender'        => $this->gender,
            'whatsapp'      => $this->whatsapp,
            'countryCode'   => $this->country_code,
            'countryCodeWA' => $this->country_code_w_a,
            'zipCode'       => $this->zip_code,
            'address'       => $this->address,
            'title'         => $this->title_en,
            'language'      => $this->language,
            'timezone'      => $this->timezone,
            'currency'      => $this->currency,
            'country'       => optional($this->country)->{'name_' . $locale},
            'state'         => optional($this->state)->{'name_' . $locale},
            'HaveCompany'   => (bool)$this->HaveCompany,
            'imgUrl'        => $this->img_url ? Storage::disk('public_uploads')->url($this->img_url) : null,
        ];
    }
}
