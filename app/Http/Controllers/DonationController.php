<?php

namespace App\Http\Controllers;

use App\Enums\DonationStatus;
use App\Http\Requests\DonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function __construct(
        protected DonationService $donationService
    ) {}

    public function create(Campaign $campaign): Response
    {
        Gate::authorize('view', $campaign);

        $bankTransferDetails = session('bank_transfer_details');
        $paymentProcessing = session('payment_processing');
        $cardRedirectUrl = session('card_redirect_url');

        session()->forget(['bank_transfer_details', 'payment_processing', 'card_redirect_url']);

        Log::info('Donation: showing form', [
            'campaign_id' => $campaign->id,
            'has_processing' => $paymentProcessing !== null,
        ]);

        return Inertia::render('campaigns/donate', [
            'campaign' => $campaign->load('owner'),
            'bank_transfer_details' => $bankTransferDetails,
            'payment_processing' => $paymentProcessing,
            'card_redirect_url' => $cardRedirectUrl,
        ]);
    }

    public function store(DonationRequest $request, Campaign $campaign)
    {
        Gate::authorize('view', $campaign);

        Log::info('Donation: form submitted', [
            'campaign_id' => $campaign->id,
            'payment_method' => $request->input('payment_method'),
        ]);

        $validated = $request->validated();

        if (
            in_array($validated['payment_method'], ['airtel_money', 'tnm_mpamba'])
            && blank($validated['donor_phone'] ?? null)
        ) {
            Log::warning('Donation: mobile money missing phone', [
                'payment_method' => $validated['payment_method'],
            ]);

            return back()->withErrors(['donor_phone' => 'Mobile money number is required for this payment method.']);
        }

        try {
            $donation = $this->donationService->createDonation($campaign, $validated);

            $cardData = $validated['payment_method'] === 'card' ? $validated : [];

            $result = $this->donationService->initiatePayment($donation, $cardData);

            if (($result['success'] ?? false) === false) {
                $errorMsg = $result['error'] ?? 'Payment initiation failed. Please try again.';

                Log::error('Donation: payment initiation failed', [
                    'donation_id' => $donation->id,
                    'error' => $errorMsg,
                ]);

                $this->donationService->markFailed($donation, $errorMsg);

                return back()
                    ->withErrors(['payment' => $errorMsg]);
            }

            $props = $result['props'];

            Log::info('Donation: payment initiated successfully', [
                'donation_id' => $donation->id,
                'props_keys' => array_keys($props),
            ]);

            $isMobileMoney = in_array($validated['payment_method'], ['airtel_money', 'tnm_mpamba']);

            if ($isMobileMoney) {
                session()->flash('flash', ['info' => 'Payment request sent. Please check your phone to approve it.']);
            }

            return Inertia::render('campaigns/donate', [
                'campaign' => $campaign->load('owner'),
                ...$props,
            ]);
        } catch (\Exception $e) {
            Log::error('Donation: processing exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['payment' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function status(Donation $donation): JsonResponse
    {
        if (! $donation->isCompleted() && $donation->payment_provider_ref) {
            $this->donationService->verifyPayment($donation);
        }

        return response()->json($this->buildPayload($donation));
    }

    public function verify(Donation $donation): JsonResponse
    {
        if ($donation->payment_provider_ref) {
            $this->donationService->verifyPayment($donation);
        }

        return response()->json([
            'donation_id' => $donation->id,
            ...$this->buildPayload($donation),
        ]);
    }

    public function cardReturn(Donation $donation): RedirectResponse
    {
        $donation->update(['status' => DonationStatus::Processing]);

        Log::info('Donation: card return received', [
            'donation_id' => $donation->id,
        ]);

        session()->flash('payment_processing', [
            'donation_id' => $donation->id,
            'payment_method' => 'card',
            'provider_ref' => $donation->payment_provider_ref,
            'amount' => $donation->amount,
            'tip_amount' => $donation->tip_amount,
            'total' => $donation->amount + $donation->tip_amount,
            'donor_phone' => $donation->donor_phone,
        ]);

        return redirect()->route('campaigns.donate', $donation->campaign);
    }

    protected function buildPayload(Donation $donation): array
    {
        $statusValue = $donation->status instanceof \BackedEnum
            ? $donation->status->value
            : (string) $donation->status;

        $paymentMethodValue = $donation->payment_method instanceof \BackedEnum
            ? $donation->payment_method->value
            : (string) $donation->payment_method;

        if ($statusValue === 'failed') {
            $errorMessage = $donation->paymentAttempts()
                ->whereNotNull('error_message')
                ->latest()
                ->value('error_message');
        }

        return [
            'status' => $statusValue,
            'status_label' => match ($statusValue) {
                'completed' => 'Completed',
                'failed' => 'Failed',
                'initiated' => 'Initiating...',
                'awaiting_customer' => 'Awaiting Approval',
                'processing' => 'Processing',
                'cancelled' => 'Cancelled',
                'expired' => 'Expired',
                default => ucfirst($statusValue),
            },
            'donation_id' => $donation->id,
            'payment_method' => $paymentMethodValue,
            'payment_method_label' => match ($paymentMethodValue) {
                'airtel_money' => 'Airtel Money',
                'tnm_mpamba' => 'TNM Mpamba',
                'card' => 'Card',
                'bank_transfer' => 'Bank Transfer',
                default => ucfirst(str_replace('_', ' ', $paymentMethodValue)),
            },
            'is_completed' => $statusValue === 'completed',
            'is_failed' => $statusValue === 'failed',
            'message' => $errorMessage ?? match ($statusValue) {
                'completed' => 'Payment completed successfully.',
                'failed' => 'The payment could not be processed. Please try again.',
                'initiated' => 'Preparing your payment...',
                'awaiting_customer' => 'Please check your phone for the payment prompt.',
                'processing' => 'Your payment is being confirmed. Please wait...',
                'cancelled' => 'This payment was cancelled.',
                'expired' => 'This payment window has expired.',
                default => 'Your payment status is being updated.',
            },
        ];
    }
}
