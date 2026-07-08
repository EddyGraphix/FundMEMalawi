<?php

namespace App\Http\Controllers;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\WebhookLog;
use App\Services\DonationService;
use App\Services\PayChanguGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected PayChanguGateway $paychangu,
        protected DonationService $donationService
    ) {}

    public function payChangu(Request $request)
    {
        $signature = $request->header('Signature', '');
        $rawPayload = $request->getContent();
        $payload = $request->all();

        Log::info('Webhook: received', [
            'event_type' => $payload['event_type'] ?? 'unknown',
            'charge_id' => $payload['charge_id'] ?? $payload['reference'] ?? null,
            'status' => $payload['status'] ?? 'unknown',
        ]);

        if (! $this->paychangu->verifyWebhookSignature($signature, $rawPayload)) {
            Log::warning('Webhook: invalid signature');

            return response()->json(['status' => 'invalid_signature']);
        }

        $chargeId = $payload['charge_id'] ?? $payload['reference'] ?? null;
        $eventType = $payload['event_type'] ?? 'unknown';

        if ($chargeId === null) {
            Log::warning('Webhook: missing charge_id');

            return response()->json(['status' => 'ignored']);
        }

        $donation = Donation::where('payment_provider_ref', $chargeId)
            ->orWhere('id', $chargeId)
            ->first();

        if ($donation === null) {
            Log::warning('Webhook: donation not found', ['charge_id' => $chargeId]);

            WebhookLog::create([
                'provider' => 'paychangu',
                'event' => $eventType,
                'payload' => $rawPayload,
                'status' => 'not_found',
            ]);

            return response()->json(['status' => 'not_found']);
        }

        if ($donation->isCompleted()) {
            Log::info('Webhook: already processed', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'already_processed']);
        }

        if ($donation->status === DonationStatus::Failed) {
            Log::info('Webhook: already failed', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'already_failed']);
        }

        $payloadStatus = $payload['status'] ?? '';

        $isPayloadSuccess = in_array($payloadStatus, ['success', 'successful', 'completed'], true);
        $isPayloadFailed = in_array($payloadStatus, ['failed', 'cancelled', 'expired'], true);

        if ($isPayloadSuccess) {
            $this->donationService->markCompleted($donation, $chargeId);

            WebhookLog::create([
                'provider' => 'paychangu',
                'event' => $eventType,
                'payload' => $rawPayload,
                'status' => 'completed',
            ]);

            Log::info('Webhook: payment completed (from payload)', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'completed', 'donation_id' => $donation->id]);
        }

        if ($isPayloadFailed) {
            $this->donationService->markFailed($donation, $payloadStatus);

            WebhookLog::create([
                'provider' => 'paychangu',
                'event' => $eventType,
                'payload' => $rawPayload,
                'status' => 'failed',
            ]);

            Log::info('Webhook: payment failed (from payload)', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'failed', 'donation_id' => $donation->id]);
        }

        $verification = $this->paychangu->verifyTransaction(
            $chargeId,
            $donation->payment_method->value
        );

        $verificationStatus = $verification['data']['status'] ?? '';

        if (($verification['success'] ?? false) && in_array($verificationStatus, ['success', 'completed', 'successful'], true)) {
            $this->donationService->markCompleted($donation, $chargeId);

            WebhookLog::create([
                'provider' => 'paychangu',
                'event' => $eventType,
                'payload' => $rawPayload,
                'status' => 'completed',
            ]);

            Log::info('Webhook: payment completed (from verification)', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'completed', 'donation_id' => $donation->id]);
        }

        if (in_array($verificationStatus, ['failed', 'cancelled', 'expired'], true)) {
            $this->donationService->markFailed($donation, $verificationStatus);

            WebhookLog::create([
                'provider' => 'paychangu',
                'event' => $eventType,
                'payload' => $rawPayload,
                'status' => 'failed',
            ]);

            Log::info('Webhook: payment failed (from verification)', ['donation_id' => $donation->id]);

            return response()->json(['status' => 'failed', 'donation_id' => $donation->id]);
        }

        Log::info('Webhook: payment pending', ['donation_id' => $donation->id]);

        WebhookLog::create([
            'provider' => 'paychangu',
            'event' => $eventType,
            'payload' => $rawPayload,
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'pending', 'donation_id' => $donation->id]);
    }
}
