<?php

namespace App\Services;

use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationService
{
    public function __construct(
        protected PayChanguGateway $paychangu
    ) {}

    public function createDonation(Campaign $campaign, array $validated): Donation
    {
        $validated['is_anonymous'] = $validated['is_anonymous'] ?? false;
        $validated['tip_amount'] = $validated['tip_amount'] ?? 0;

        $donation = $campaign->donations()->create([
            'donor_id' => auth()->id(),
            'donor_name' => $validated['donor_name'],
            'donor_email' => $validated['donor_email'],
            'donor_phone' => $validated['donor_phone'] ?? null,
            'is_anonymous' => $validated['is_anonymous'],
            'amount' => $validated['amount'],
            'tip_amount' => $validated['tip_amount'],
            'currency' => 'MWK',
            'payment_method' => $validated['payment_method'],
            'status' => DonationStatus::Initiated,
            'message' => $validated['message'] ?? null,
        ]);

        Log::info('Donation: record created', [
            'donation_id' => $donation->id,
            'campaign_id' => $campaign->id,
            'payment_method' => $validated['payment_method'],
            'total_amount' => $donation->amount + $donation->tip_amount,
        ]);

        return $donation;
    }

    public function initiatePayment(Donation $donation, array $cardData = []): array
    {
        $method = $donation->payment_method->value;

        Log::info('Payment: initiating', [
            'donation_id' => $donation->id,
            'method' => $method,
        ]);

        $result = match ($method) {
            'airtel_money' => $this->processMobileMoney($donation, $donation->donor_phone, 'AIRTEL'),
            'tnm_mpamba' => $this->processMobileMoney($donation, $donation->donor_phone, 'TNM'),
            'card' => $this->processCardPayment($donation, $cardData),
            'bank_transfer' => $this->processBankTransfer($donation),
            default => ['success' => false, 'error' => 'Unsupported payment method.'],
        };

        Log::info('Payment: initiation result', [
            'donation_id' => $donation->id,
            'success' => $result['success'] ?? false,
            'has_props' => isset($result['props']),
        ]);

        return $result;
    }

    public function markCompleted(Donation $donation, ?string $providerRef = null): void
    {
        DB::transaction(function () use ($donation, $providerRef) {
            $donation->update([
                'status' => DonationStatus::Completed,
                'payment_provider' => 'paychangu',
                'payment_provider_ref' => $providerRef ?? $donation->payment_provider_ref,
            ]);

            $donation->campaign()->increment('raised_amount', $donation->amount);

            Log::info('Donation: completed', [
                'donation_id' => $donation->id,
                'campaign_id' => $donation->campaign_id,
                'amount' => $donation->amount,
            ]);
        });
    }

    public function markFailed(Donation $donation, ?string $reason = null): void
    {
        DB::transaction(function () use ($donation) {
            $donation->update(['status' => DonationStatus::Failed]);

            Log::info('Donation: marked as failed', [
                'donation_id' => $donation->id,
            ]);
        });
    }

    public function verifyPayment(Donation $donation): void
    {
        if ($donation->isCompleted() || $donation->status === DonationStatus::Failed) {
            return;
        }

        $providerRef = $donation->payment_provider_ref;

        if ($providerRef === null) {
            Log::warning('Payment: cannot verify, no provider ref', [
                'donation_id' => $donation->id,
            ]);

            return;
        }

        $result = $this->paychangu->verifyTransaction($providerRef, $donation->payment_method->value);
        $apiStatus = $result['data']['status'] ?? null;

        if (($result['success'] ?? false) && in_array($apiStatus, ['success', 'completed', 'successful'], true)) {
            $this->markCompleted($donation);

            Log::info('Payment: verified as completed', [
                'donation_id' => $donation->id,
            ]);

            return;
        }

        if (in_array($apiStatus, ['failed', 'cancelled', 'expired'], true)) {
            $this->markFailed($donation);

            Log::info('Payment: verified as failed', [
                'donation_id' => $donation->id,
                'provider_status' => $apiStatus,
            ]);

            return;
        }

        if ($result['success'] === false && $apiStatus === 'pending') {
            $firstLog = $result['data']['logs'][0] ?? null;

            if (($firstLog['type'] ?? null) === 'error') {
                $gatewayMessage = $firstLog['message'] ?? 'Gateway rejected the transaction';

                $this->logPaymentAttempt(
                    donation: $donation,
                    status: 'failed',
                    providerRef: $providerRef,
                    errorMessage: $gatewayMessage,
                    responseData: $result,
                );

                Log::info('Payment: gateway rejected, marking as failed', [
                    'donation_id' => $donation->id,
                    'gateway_message' => $gatewayMessage,
                ]);

                $this->markFailed($donation);

                return;
            }
        }
    }

    public function logPaymentAttempt(
        Donation $donation,
        string $status,
        ?string $providerRef = null,
        ?string $errorMessage = null,
        array $requestData = [],
        array $responseData = [],
    ): void {
        PaymentAttempt::create([
            'donation_id' => $donation->id,
            'provider' => 'paychangu',
            'provider_ref' => $providerRef,
            'status' => $status,
            'request_data' => json_encode($requestData),
            'response_data' => json_encode($responseData),
            'error_message' => $errorMessage,
        ]);
    }

    protected function processMobileMoney(Donation $donation, ?string $phone, string $network): array
    {
        if (blank($phone)) {
            return ['success' => false, 'error' => 'Mobile money number is required.'];
        }

        $response = $this->paychangu->createMobileMoneyPayment($donation, $phone, $network);

        $providerRef = $response['data']['charge_id'] ?? null;

        $firstLog = $response['data']['logs'][0] ?? null;
        $gatewayError = ($firstLog['type'] ?? null) === 'error' ? ($firstLog['message'] ?? null) : null;

        if ($gatewayError !== null) {
            $this->logPaymentAttempt(
                donation: $donation,
                status: 'failed',
                providerRef: $providerRef,
                errorMessage: $gatewayError,
                requestData: ['network' => $network, 'phone' => $phone],
                responseData: $response,
            );

            $donation->update([
                'payment_provider_ref' => $providerRef,
                'status' => DonationStatus::Failed,
            ]);

            return ['success' => false, 'error' => $gatewayError];
        }

        $this->logPaymentAttempt(
            donation: $donation,
            status: ($response['success'] ?? false) ? 'pending' : 'failed',
            providerRef: $providerRef,
            errorMessage: ! ($response['success'] ?? false) ? ($response['error'] ?? 'Unknown error') : null,
            requestData: ['network' => $network, 'phone' => $phone],
            responseData: $response,
        );

        if (! ($response['success'] ?? false)) {
            return ['success' => false, 'error' => $response['error'] ?? 'Mobile money payment failed.'];
        }

        $donation->update([
            'payment_provider_ref' => $providerRef,
            'status' => DonationStatus::AwaitingCustomer,
        ]);

        return [
            'success' => true,
            'props' => [
                'payment_processing' => [
                    'donation_id' => $donation->id,
                    'payment_method' => $donation->payment_method->value,
                    'provider_ref' => $providerRef,
                    'amount' => $donation->amount,
                    'tip_amount' => $donation->tip_amount,
                    'total' => $donation->amount + $donation->tip_amount,
                    'donor_phone' => $donation->donor_phone,
                ],
            ],
        ];
    }

    protected function processCardPayment(Donation $donation, array $cardData): array
    {
        $response = $this->paychangu->createCardPayment($donation, $cardData);

        $chargeId = $response['data']['charge_id'] ?? null;
        $redirectUrl = $response['data']['3ds_auth_link'] ?? null;

        $this->logPaymentAttempt(
            donation: $donation,
            status: ($response['success'] ?? false) ? 'pending' : 'failed',
            providerRef: $chargeId,
            errorMessage: ! ($response['success'] ?? false) ? ($response['error'] ?? 'Card payment failed.') : null,
            requestData: ['method' => 'direct_card_charge'],
            responseData: $response,
        );

        if (! ($response['success'] ?? false)) {
            return ['success' => false, 'error' => $response['error'] ?? 'Card payment failed.'];
        }

        $donation->update([
            'payment_provider_ref' => $chargeId,
            'status' => DonationStatus::AwaitingCustomer,
        ]);

        return [
            'success' => true,
            'props' => [
                'payment_processing' => [
                    'donation_id' => $donation->id,
                    'payment_method' => 'card',
                    'provider_ref' => $chargeId,
                    'amount' => $donation->amount,
                    'tip_amount' => $donation->tip_amount,
                    'total' => $donation->amount + $donation->tip_amount,
                    'donor_phone' => $donation->donor_phone,
                ],
                'card_redirect_url' => $redirectUrl,
            ],
        ];
    }

    protected function processBankTransfer(Donation $donation): array
    {
        $response = $this->paychangu->createBankTransfer($donation);

        $providerRef = $response['data']['transaction']['charge_id'] ?? $response['data']['charge_id'] ?? null;
        $bankDetails = $response['data']['payment_account_details'] ?? $response['data'] ?? [];

        $this->logPaymentAttempt(
            donation: $donation,
            status: ($response['success'] ?? false) ? 'pending' : 'failed',
            providerRef: $providerRef,
            errorMessage: ! ($response['success'] ?? false) ? ($response['error'] ?? 'Bank transfer failed.') : null,
            requestData: ['payment_method' => 'mobile_bank_transfer'],
            responseData: $response,
        );

        if (! ($response['success'] ?? false)) {
            return ['success' => false, 'error' => $response['error'] ?? 'Bank transfer initiation failed.'];
        }

        $donation->update([
            'payment_provider_ref' => $providerRef,
            'status' => DonationStatus::AwaitingCustomer,
        ]);

        return [
            'success' => true,
            'props' => [
                'payment_processing' => [
                    'donation_id' => $donation->id,
                    'payment_method' => 'bank_transfer',
                    'provider_ref' => $providerRef,
                    'amount' => $donation->amount,
                    'tip_amount' => $donation->tip_amount,
                    'total' => $donation->amount + $donation->tip_amount,
                    'donor_phone' => $donation->donor_phone,
                ],
                'bank_transfer_details' => $bankDetails,
            ],
        ];
    }
}
