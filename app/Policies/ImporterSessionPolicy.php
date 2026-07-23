<?php

namespace App\Policies;

use App\User;
use App\Services\Importer\ImporterSession;

class ImporterSessionPolicy
{
    public function access(User $user, ImporterSession $session): bool
    {
        return $session->user_id === $user->id;
    }
}
