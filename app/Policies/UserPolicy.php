<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{


    public function index(User $user)
    {
        return $user->ableTo('users.index');
    }
    /**
     *
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?User $model = null): bool
    {
        if (!$user->ableTo('users.show')) {
            return false;
        }

        // Without a target this is only the permission check (kept for existing callers).
        return $model === null
            || $user->id === $model->id
            || $this->inScope($user, $model);
    }

    /**
     * Determine whether the user can read the model's profile through the API (contact
     * details, address, startup, mentorship sessions): the account's owner, or staff
     * managing the organization the account belongs to (directly, or through its
     * startup's program). Platform admins manage every organization.
     *
     * Not gated on users.show, so everyone can still read their own profile; and
     * managesOrganization rather than ownsOrganization, because a founder "owns" their
     * program's organization but must not read its staff or the program's other founders.
     */
    public function viewProfile(User $user, User $model): bool
    {
        return $user->id === $model->id
            || $user->managesOrganization($model->organization_id)
            || $user->managesOrganization($model->company?->cycledata?->organization_id);
    }

    /**
     * The target account belongs to the acting user's tenant scope: platform admins
     * reach everyone, others only users of an organization they own (directly, or
     * through the user's company program).
     */
    private function inScope(User $user, User $model): bool
    {
        return $user->hasRole('admin')
            || $user->ownsOrganization($model->organization_id)
            || $user->ownsOrganization($model->company?->cycledata?->organization_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->ableTo('users.create');

    }

    /**
     * Determine whether the user can create models.
     */
    public function store(User $user): bool
    {
        return $user->ableTo('users.store');

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if (!$user->ableToAny(['users.edit', 'users.update'])) {
            return false;
        }

        // Self-service edits, admins, or managers acting within their org scope.
        return $user->id === $model->id
            || $user->hasRole('admin')
            || $user->ownsOrganization($model->organization_id)
            || $user->ownsOrganization($model->company?->cycledata?->organization_id);
    }

    /**
     * Determine whether the user can Deactivate the model.
     */
    public function deactivate(User $user, User $model): bool
    {
        if (!$user->ableToAny(['users.edit', 'users.update'])) {
            return false;
        }

        // No self-service clause: a user must not deactivate their own account here.
        return $user->hasRole('admin')
            || $user->ownsOrganization($model->organization_id)
            || $user->ownsOrganization($model->company?->cycledata?->organization_id);
    }
    /**
     * Determine whether the user can update the model.
     * @param \App\User $user
     * @param \App\User $model
     * @return bool
     */
    public function updatePassword(User $user, User $model): bool
    {
        return $user->ableTo('users.update')
            && ($user->id === $model->id || $this->inScope($user, $model));
    }
    /**
     * Determine whether the user can update his own password.
     * @param \App\User $user
     * @param \App\User $model
     * @return bool
     */
    public function updateMyPassword(User $user, User $model): bool
    {
        return $user->ableTo('user.profile_update_my_password');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, User $model): bool
    {
         return $user->ableTo('users.destroy') && $this->inScope($user, $model);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        //
    }
}
