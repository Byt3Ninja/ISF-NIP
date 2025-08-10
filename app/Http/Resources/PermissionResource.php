<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissionsByGroup = [];

        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permission) {
            $groupName = optional($permission->permissionsGroup)->name ?? 'general';
            $permissionsByGroup[$groupName][] = $permission->name;
        }

        foreach ($permissionsByGroup as $group => &$groupPermissions) {
            $groupPermissions = array_unique($groupPermissions);
        }

        return [
            'userId' => $this->id,
            'roles' => $this->roles->pluck('name')->toArray(),
            'permissions' => $permissionsByGroup
        ];
    }
}
