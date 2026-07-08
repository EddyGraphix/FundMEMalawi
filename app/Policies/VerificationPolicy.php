<?php

namespace App\Policies;

use App\Models\User;

class VerificationPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user): bool
    {
        return $user->hasPermissionTo('review verifications');
    }
}
