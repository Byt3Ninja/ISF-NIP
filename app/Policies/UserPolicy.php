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
    public function view(User $user): bool
    {
        return $user->ableTo('users.show');
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
        return $user->ableToAny(['users.edit', 'users.update']);
    }

    /**
     * Determine whether the user can Deactivate the model.
     */
    public function deactivate(User $user, User $model): bool
    {
        return $user->ableToAny(['users.edit', 'users.update']);
    }
    /**
     * Determine whether the user can update the model.
     * @param \App\User $user
     * @param \App\User $model
     * @return bool
     */
    public function updatePassword(User $user, User $model): bool
    {
        return $user->ableTo('users.update');
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
         return $user->ableTo('users.destroy');
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
