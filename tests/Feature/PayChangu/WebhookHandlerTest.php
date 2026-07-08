<?php

use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Models\WebhookLog;
use Paychangu\Laravel\Paychangu;

uses()->group('webhook-handler');

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
    config(['paychangu.secret' => 'test-secret']);
});

function validSignature(string $payload): string
{
    return hash_hmac('sha256', $payload, 'test-secret');
}

it('processes a completed payment webhook from payload status', function () {
    $chargeId = 'PC-WEBHOOK-COMPLETE';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'amount' => 100,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'success',
        'amount' => 100,
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'completed', 'donation_id' => $donation->id]);

    expect($donation->fresh()->status->value)->toBe('completed');
    expect((float) $donation->fresh()->campaign->raised_amount)->toBe(100.0);
});

it('processes a completed webhook with successful status', function () {
    $chargeId = 'PC-WEBHOOK-SUCCESSFUL';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'amount' => 50,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'successful',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()->assertJson(['status' => 'completed']);

    expect($donation->fresh()->status->value)->toBe('completed');
});

it('processes a completed webhook with completed status', function () {
    $chargeId = 'PC-WEBHOOK-COMPLETED';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'amount' => 75,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'completed',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()->assertJson(['status' => 'completed']);

    expect($donation->fresh()->status->value)->toBe('completed');
});

it('processes a failed payment webhook from payload status', function () {
    $chargeId = 'PC-WEBHOOK-FAIL';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'failed',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'failed', 'donation_id' => $donation->id]);

    expect($donation->fresh()->status->value)->toBe('failed');
});

it('rejects webhook with invalid signature', function () {
    $payload = ['event_type' => 'api.charge.payment', 'charge_id' => 'test'];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => 'invalid-signature',
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'invalid_signature']);
});

it('returns already processed for completed donation', function () {
    $chargeId = 'PC-WEBHOOK-ALREADY';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Completed,
        'payment_provider_ref' => $chargeId,
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'success',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'already_processed']);
});

it('returns not found when donation does not exist', function () {
    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => 'non-existent',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'not_found']);

    expect(WebhookLog::where('event', 'api.charge.payment')->exists())->toBeTrue();
});

it('falls back to verification API when payload status is unknown', function () {
    $chargeId = 'PC-WEBHOOK-UNKNOWN';

    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('verify_mobile_money_payment')
        ->with($chargeId)
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'completed'],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'airtel_money',
        'amount' => 150,
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'unknown_status',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()->assertJson(['status' => 'completed']);

    expect($donation->fresh()->status->value)->toBe('completed');
    expect((float) $donation->fresh()->campaign->raised_amount)->toBe(150.0);
});

it('returns pending when payload and verification both show pending', function () {
    $chargeId = 'PC-WEBHOOK-PENDING';

    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('verify_mobile_money_payment')
        ->with($chargeId)
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'pending'],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'pending',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'pending', 'donation_id' => $donation->id]);

    expect($donation->fresh()->status->value)->not->toBe('completed');
});

it('processes card webhook from payload status without verification call', function () {
    $chargeId = 'PC-CARD-WEBHOOK';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'card',
        'amount' => 200,
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'successful',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()->assertJson(['status' => 'completed']);

    expect($donation->fresh()->status->value)->toBe('completed');
});

it('processes bank transfer webhook from payload status', function () {
    $chargeId = 'PC-BT-WEBHOOK';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'bank_transfer',
        'amount' => 300,
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'completed',
    ];

    $response = $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    $response->assertOk()->assertJson(['status' => 'completed']);

    expect($donation->fresh()->status->value)->toBe('completed');
});

it('logs webhook to webhook_logs table on completion', function () {
    $chargeId = 'PC-LOG-TEST';

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_provider_ref' => $chargeId,
        'payment_method' => 'airtel_money',
    ]);

    $payload = [
        'event_type' => 'api.charge.payment',
        'charge_id' => $chargeId,
        'status' => 'successful',
    ];

    $this->postJson('/webhook/paychangu', $payload, [
        'Signature' => validSignature(json_encode($payload)),
    ]);

    expect(WebhookLog::where('event', 'api.charge.payment')->where('status', 'completed')->count())->toBe(1);
});
