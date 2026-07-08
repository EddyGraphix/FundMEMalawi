<?php

namespace App\Jobs;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerifyDonationPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Donation $donation
    ) {}

    public function handle(DonationService $donationService): void
    {
        if ($this->donation->isCompleted() || $this->donation->status === DonationStatus::Failed) {
            Log::info('VerifyDonationPayment: already terminal', [
                'donation_id' => $this->donation->id,
                'status' => $this->donation->status->value,
            ]);

            return;
        }

        Log::info('VerifyDonationPayment: starting verification', [
            'donation_id' => $this->donation->id,
            'provider_ref' => $this->donation->payment_provider_ref,
        ]);

        $donationService->verifyPayment($this->donation);
    }
}
