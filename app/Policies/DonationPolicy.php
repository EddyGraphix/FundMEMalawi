<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function view(?User $user, Donation $donation): bool
    {
        if (! $user) {
            return false;
        }

        return $user->id === $donation->donor_id
            || $user->id === $donation->campaign->owner_id
            || $user->hasPermissionTo('manage campaigns');
    }

    public function create(?User $user): bool
    {
        return true;
    }
}
