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

        $permissions = $this->permissions;

        foreach ($permissions as $permission) {
                $groupName = optional($permission->permissionsGroup)->name ?? 'general';
                $permissionsByGroup[$groupName][] = $permission->name;
        }

            foreach ($permissionsByGroup as &$groupPermissions) {
                $groupPermissions = array_unique($groupPermissions);
            }

            return [
                'roleId' => $this->id,
                'roleName' => $this->name,
                'permissions' => $permissionsByGroup
            ];
    }
}
