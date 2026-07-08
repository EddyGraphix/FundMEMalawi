<?php

namespace App\Services;

use App\Models\Campaign;
use Illuminate\Support\Facades\URL;

class BeneficiaryInviteService
{
    public function generateInviteLink(Campaign $campaign): string
    {
        return URL::signedRoute(
            'beneficiary.invite',
            ['campaign' => $campaign->id],
            now()->addDays(7)
        );
    }

    public function verifyInviteLink(Campaign $campaign, string $signature): bool
    {
        return URL::hasValidSignature(
            request()->fullUrl()
        );
    }
}
