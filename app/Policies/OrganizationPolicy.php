<?php

namespace App\Policies;

use App\User;
use App\Organization;

class OrganizationPolicy
{
    public function viewAny(User $user)
    {
        return $user->ableTo('organizations.index');
    }

    public function create(User $user)
    {
        return $user->ableToAny(['organizations.create', 'organizations.store']);
    }

    public function update(User $user, Organization $organization)
    {
        return $user->ableToAny(['organizations.edit', 'organizations.update'])
            && ($user->hasRole('admin') || $user->ownsOrganization($organization->id));
    }

    public function delete(User $user, Organization $organization)
    {
        return $user->ableTo('organizations.destroy')
            && ($user->hasRole('admin') || $user->ownsOrganization($organization->id));
    }
}
