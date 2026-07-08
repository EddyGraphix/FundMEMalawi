<?php

use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\PaymentAttempt;
use App\Models\User;
use Paychangu\Laravel\Paychangu;

uses()->group('donation-payment-flow');

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
});

it('creates donation with initiated status and returns processing props', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('mobile_money_operators')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                ['ref_id' => '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'short_code' => 'airtel', 'name' => 'Airtel Money'],
            ],
        ]);
    $paychangu->shouldReceive('create_mobile_money_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['charge_id' => 'PC-FLOW-001', 'ref_id' => 'ref-123', 'status' => 'pending'],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => 'Test User',
        'donor_email' => 'test@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertInertia(fn ($page) => $page
        ->component('campaigns/donate')
        ->has('payment_processing')
    );

    expect(PaymentAttempt::count())->toBe(1);
    expect(Donation::first()->status->value)->toBe('awaiting_customer');
});

it('handles mobile money initiation failure with error', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('mobile_money_operators')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                ['ref_id' => '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'short_code' => 'airtel', 'name' => 'Airtel Money'],
            ],
        ]);
    $paychangu->shouldReceive('create_mobile_money_payment')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Payment prompt could not be created.',
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'tip_amount' => 0,
        'donor_name' => 'Test Donor',
        'donor_email' => 'donor@example.com',
        'donor_phone' => '0991234567',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertSessionHasErrors('payment');
    expect(Donation::first()->status->value)->toBe('failed');
});

it('completes donation via verify endpoint', function () {
    $chargeId = 'PC-VERIFY-FLOW';

    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('verify_mobile_money_payment')
        ->with($chargeId)
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'success'],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_method' => 'airtel_money',
        'payment_provider_ref' => $chargeId,
        'amount' => 100,
    ]);

    $response = $this->post(route('donations.verify', $donation));

    $response->assertOk()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('is_completed', true);

    expect($donation->fresh()->status)->toBe(DonationStatus::Completed);
});

it('returns correct status payload for initiated donation', function () {
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
        'payment_method' => 'card',
    ]);

    $response = $this->get(route('donations.status', $donation));

    $response->assertOk()
        ->assertJson([
            'donation_id' => $donation->id,
            'status' => 'initiated',
            'payment_method' => 'card',
            'is_completed' => false,
            'is_failed' => false,
        ]);
});

it('handles card flow with 3DS redirect', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_card_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => 'PC-CARD-3DS-FLOW',
                'orderReference' => 'ref-3ds',
                '3ds_auth_link' => 'https://3ds.paychangu.com/auth/xyz',
                'status' => 'pending',
            ],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 100,
        'donor_name' => 'Test User',
        'donor_email' => 'test@example.com',
        'payment_method' => 'card',
        'card_number' => '4111111111111111',
        'expiry' => '12/28',
        'cvv' => '123',
        'cardholder_name' => 'Test User',
    ]);

    $response->assertInertia(fn ($page) => $page
        ->component('campaigns/donate')
        ->has('payment_processing')
        ->has('card_redirect_url')
    );

    expect(Donation::first()->status->value)->toBe('awaiting_customer');
});

it('handles bank transfer flow', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_direct_charge_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => 'PC-BT-FLOW',
                'payment_account_details' => [
                    'bank_name' => 'National Bank of Malawi',
                    'account_number' => '1234567890',
                ],
                'transaction' => [
                    'charge_id' => 'PC-BT-FLOW',
                    'status' => 'pending',
                ],
            ],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 100,
        'donor_name' => 'Test User',
        'donor_email' => 'test@example.com',
        'payment_method' => 'bank_transfer',
    ]);

    $response->assertInertia(fn ($page) => $page
        ->component('campaigns/donate')
        ->has('payment_processing')
        ->has('bank_transfer_details')
    );

    expect(Donation::first()->status->value)->toBe('awaiting_customer');
    expect(PaymentAttempt::count())->toBe(1);
});

it('handles card charge failure', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_card_payment')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Card charge failed',
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 100,
        'donor_name' => 'Test User',
        'donor_email' => 'test@example.com',
        'payment_method' => 'card',
        'card_number' => '4111111111111111',
        'expiry' => '12/28',
        'cvv' => '123',
        'cardholder_name' => 'Test User',
    ]);

    $response->assertSessionHasErrors('payment');
    expect(Donation::first()->status->value)->toBe('failed');
    expect(PaymentAttempt::where('status', 'failed')->count())->toBe(1);
});

it('handles bank transfer failure', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_direct_charge_payment')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Bank transfer service error',
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 100,
        'donor_name' => 'Test User',
        'donor_email' => 'test@example.com',
        'payment_method' => 'bank_transfer',
    ]);

    $response->assertSessionHasErrors('payment');
    expect(Donation::first()->status->value)->toBe('failed');
});

it('card-return endpoint sets processing status and redirects', function () {
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::AwaitingCustomer,
        'payment_method' => 'card',
        'payment_provider_ref' => 'PC-CARD-RETURN',
    ]);

    $response = $this->get(route('donations.card-return', $donation));

    $response->assertRedirect(route('campaigns.donate', $this->campaign));
    $response->assertSessionHas('payment_processing');

    expect($donation->fresh()->status->value)->toBe('processing');
});

it('guests can donate without logging in', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('mobile_money_operators')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                ['ref_id' => '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'short_code' => 'airtel', 'name' => 'Airtel Money'],
            ],
        ]);
    $paychangu->shouldReceive('create_mobile_money_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['charge_id' => 'PC-GUEST-001', 'status' => 'pending'],
        ]);

    $this->app->instance(Paychangu::class, $paychangu);

    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 25,
        'donor_name' => 'Guest Donor',
        'donor_email' => 'guest@example.com',
        'donor_phone' => '0999000000',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertInertia(fn ($page) => $page
        ->component('campaigns/donate')
        ->has('payment_processing')
    );
    expect(Donation::first()->donor_id)->toBeNull();
});
