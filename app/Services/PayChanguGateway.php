<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use Paychangu\Laravel\Paychangu;

class PayChanguGateway
{
    protected ?array $cachedOperators = null;

    public function __construct(
        protected Paychangu $paychangu
    ) {}

    public function createMobileMoneyPayment(Donation $donation, string $phone, string $network): array
    {
        Log::info('PayChangu: initiating mobile money payment', [
            'donation_id' => $donation->id,
            'network' => $network,
            'amount' => $donation->amount + $donation->tip_amount,
        ]);

        $operatorRefId = $this->resolveOperatorRefId($network);

        if ($operatorRefId === null) {
            Log::error('PayChangu: unsupported mobile money network', ['network' => $network]);

            return ['success' => false, 'error' => "Unsupported mobile money network: {$network}"];
        }

        try {
            $response = $this->paychangu->create_mobile_money_payment([
                'mobile_money_operator_ref_id' => $operatorRefId,
                'mobile' => $phone,
                'amount' => (string) ($donation->amount + $donation->tip_amount),
                'charge_id' => (string) $donation->id,
            ]);

            Log::info('PayChangu: mobile money response', [
                'donation_id' => $donation->id,
                'response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayChangu: mobile money exception', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Payment service is temporarily unavailable. Please try again.'];
        }
    }

    public function createCardPayment(Donation $donation, array $cardData): array
    {
        Log::info('PayChangu: initiating card payment', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount + $donation->tip_amount,
        ]);

        try {
            $response = $this->paychangu->create_card_payment([
                'card_number' => $cardData['card_number'],
                'expiry' => $cardData['expiry'],
                'cvv' => $cardData['cvv'],
                'cardholder_name' => $cardData['cardholder_name'],
                'amount' => (string) ($donation->amount + $donation->tip_amount),
                'currency' => $donation->currency,
                'charge_id' => (string) $donation->id,
                'redirect_url' => route('donations.card-return', $donation),
                'email' => $donation->donor_email,
            ]);

            Log::info('PayChangu: card payment response', [
                'donation_id' => $donation->id,
                'response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayChangu: card payment exception', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Payment service is temporarily unavailable. Please try again.'];
        }
    }

    public function createBankTransfer(Donation $donation): array
    {
        Log::info('PayChangu: initiating bank transfer', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount + $donation->tip_amount,
        ]);

        try {
            $response = $this->paychangu->create_direct_charge_payment([
                'amount' => (string) ($donation->amount + $donation->tip_amount),
                'currency' => $donation->currency,
                'charge_id' => (string) $donation->id,
                'payment_method' => 'mobile_bank_transfer',
                'customer' => [
                    'name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                ],
            ]);

            Log::info('PayChangu: bank transfer response', [
                'donation_id' => $donation->id,
                'response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayChangu: bank transfer exception', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Payment service is temporarily unavailable. Please try again.'];
        }
    }

    public function verifyTransaction(string $providerRef, string $paymentMethod): array
    {
        Log::info('PayChangu: verifying transaction', [
            'provider_ref' => $providerRef,
            'payment_method' => $paymentMethod,
        ]);

        try {
            $result = match ($paymentMethod) {
                'airtel_money', 'tnm_mpamba' => $this->paychangu->verify_mobile_money_payment($providerRef),
                'card' => $this->paychangu->verify_card_payment($providerRef),
                'bank_transfer' => $this->paychangu->get_direct_charge_details($providerRef),
                default => ['success' => false, 'error' => 'Unknown payment method'],
            };

            Log::info('PayChangu: verification response', [
                'provider_ref' => $providerRef,
                'result' => $result,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('PayChangu: verification exception', [
                'provider_ref' => $providerRef,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $secret = config('paychangu.secret');

        if ($secret === null || $secret === '') {
            Log::warning('PayChangu: webhook secret not configured');

            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);
        $isValid = hash_equals($computed, $signature);

        Log::info('PayChangu: webhook signature verification', [
            'is_valid' => $isValid,
        ]);

        return $isValid;
    }

    protected function resolveOperatorRefId(string $network): ?string
    {
        if ($this->cachedOperators === null) {
            try {
                $this->cachedOperators = $this->paychangu->mobile_money_operators();
            } catch (\Exception $e) {
                Log::error('PayChangu: mobile money operators exception', [
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        }

        if (! ($this->cachedOperators['success'] ?? false)) {
            Log::error('PayChangu: failed to fetch mobile money operators');

            return null;
        }

        foreach ($this->cachedOperators['data'] as $operator) {
            if (strtoupper($operator['short_code'] ?? '') === strtoupper($network)) {
                return $operator['ref_id'];
            }
        }

        Log::error('PayChangu: network not found in operators', [
            'network' => $network,
            'available' => array_map(fn ($o) => $o['short_code'] ?? null, $this->cachedOperators['data'] ?? []),
        ]);

        return null;
    }
}
