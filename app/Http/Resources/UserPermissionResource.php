<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'assignedTo' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permission_group_id' => $this->permission_group_id,
            'groupName' => $this->permissionsGroup?->name,
            'is_core' => $this->is_core,
        ];
    }
}
