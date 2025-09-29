<?php

namespace App\Policies;

use App\Currency;
use App\User;
use Illuminate\Auth\Access\Response;

class CurrencyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->ableTo('currencies.index');
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->ableToAny(['currencies.create', 'currencies.store']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Currency $currency)
    {
        return $user->ableToAny(['currencies.edit', 'currencies.update']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Currency $currency)
    {
        return $user->ableTo('currencies.destroy');
    }

     public function updateRates(User $user)
    {
        return $user->ableTo('currencies.rates_update_all');
    }

}
