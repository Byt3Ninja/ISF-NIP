<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class UserResource extends JsonResource
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
            'firstName' => $this->first_name_en,
            'lastName' => $this->last_name_en,
            'email' => $this->email,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'role' => $this->getRoleNames()->first(),
            'haveCompany' => (bool) $this->haveCompany,
            'imgUrl' => $this->img_url ? Storage::disk('public_uploads')->url($this->img_url) : null,
            'createdAt' => $this->created_at->toDateTimeString(),
            'status' => $this->status,
        ];
    }
}
