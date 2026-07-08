<?php

namespace App\Policies;

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\Campaign;
use App\Models\User;

class WithdrawalPolicy
{
    public function create(User $user, Campaign $campaign): bool
    {
        if ($campaign->owner_id !== $user->id) {
            return false;
        }

        $isVerified = $user->verifications()
            ->where('type', VerificationType::NationalId)
            ->where('status', VerificationStatus::Approved)
            ->exists();

        if (! $isVerified) {
            return false;
        }

        if ($campaign->beneficiary_id) {
            $beneficiary = User::find($campaign->beneficiary_id);

            if ($beneficiary) {
                $beneficiaryVerified = $beneficiary->verifications()
                    ->where('type', VerificationType::NationalId)
                    ->where('status', VerificationStatus::Approved)
                    ->exists();

                if (! $beneficiaryVerified) {
                    return false;
                }
            }
        }

        return true;
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('manage withdrawals');
    }
}
