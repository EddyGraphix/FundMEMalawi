<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function view(?User $user, Campaign $campaign): bool
    {
        if ($campaign->status === CampaignStatus::Active) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $campaign->owner_id || $user->hasPermissionTo('approve campaigns');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->verifications()
            ->where('type', VerificationType::NationalId)
            ->where('status', VerificationStatus::Approved)
            ->exists();
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->owner_id || $user->hasPermissionTo('manage campaigns');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->owner_id || $user->hasPermissionTo('manage campaigns');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('approve campaigns');
    }
}
