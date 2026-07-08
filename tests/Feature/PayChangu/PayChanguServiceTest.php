<?php

use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\PayChanguGateway;
use Paychangu\Laravel\Paychangu;

uses()->group('paychangu-service');

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
});

it('initiates mobile money payment', function () {
    $chargeId = 'PC-MM-'.rand(1000, 9999);

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
        ->with(Mockery::on(fn ($data) => $data['mobile'] === '0999123456' && $data['amount'] === '150'))
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => $chargeId,
                'ref_id' => (string) rand(100000, 999999),
                'status' => 'pending',
            ],
        ]);

    $gateway = new PayChanguGateway($paychangu);
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
        'amount' => 150,
        'tip_amount' => 0,
    ]);

    $result = $gateway->createMobileMoneyPayment($donation, '0999123456', 'AIRTEL');

    expect($result['success'])->toBeTrue();
    expect($result['data']['charge_id'])->toBe($chargeId);
});

it('returns error for unsupported mobile money network', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('mobile_money_operators')
        ->once()
        ->andReturn(['success' => false]);

    $gateway = new PayChanguGateway($paychangu);
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
    ]);

    $result = $gateway->createMobileMoneyPayment($donation, '0999123456', 'MTN');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Unsupported');
});

it('initiates card payment', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_card_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => 'PC-CARD-123',
                'orderReference' => 'ref-xyz',
                'status' => 'pending',
            ],
        ]);

    $gateway = new PayChanguGateway($paychangu);
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
        'amount' => 100,
    ]);

    $result = $gateway->createCardPayment($donation, [
        'card_number' => '4111111111111111',
        'expiry' => '12/28',
        'cvv' => '123',
        'cardholder_name' => 'Test User',
    ]);

    expect($result['success'])->toBeTrue();
});

it('initiates card payment with 3DS redirect', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_card_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => 'PC-CARD-3DS',
                'orderReference' => 'ref-3ds',
                '3ds_auth_link' => 'https://3ds.paychangu.com/auth/xyz',
                'status' => 'pending',
            ],
        ]);

    $gateway = new PayChanguGateway($paychangu);
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
        'amount' => 100,
    ]);

    $result = $gateway->createCardPayment($donation, [
        'card_number' => '4111111111111111',
        'expiry' => '12/28',
        'cvv' => '123',
        'cardholder_name' => 'Test User',
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['data']['3ds_auth_link'])->toBe('https://3ds.paychangu.com/auth/xyz');
});

it('initiates bank transfer payment', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('create_direct_charge_payment')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'charge_id' => 'PC-BT-789',
                'payment_account_details' => [
                    'bank_name' => 'National Bank of Malawi',
                    'account_number' => '1234567890',
                    'account_name' => 'PayChangu',
                ],
                'transaction' => [
                    'amount' => 100,
                    'charge_id' => 'PC-BT-789',
                    'status' => 'pending',
                ],
            ],
        ]);

    $gateway = new PayChanguGateway($paychangu);
    $donation = Donation::factory()->create([
        'campaign_id' => $this->campaign->id,
        'status' => DonationStatus::Initiated,
        'amount' => 100,
    ]);

    $result = $gateway->createBankTransfer($donation);

    expect($result['success'])->toBeTrue();
});

it('verifies transaction with correct method based on payment method', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('verify_mobile_money_payment')
        ->with('charge-123')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'success', 'amount' => 100],
        ]);

    $gateway = new PayChanguGateway($paychangu);

    $result = $gateway->verifyTransaction('charge-123', 'airtel_money');

    expect($result['success'])->toBeTrue();
    expect($result['data']['status'])->toBe('success');
});

it('verifies card payment through card verification endpoint', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('verify_card_payment')
        ->with('charge-card-456')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'successful', 'amount' => 100],
        ]);

    $gateway = new PayChanguGateway($paychangu);

    $result = $gateway->verifyTransaction('charge-card-456', 'card');

    expect($result['success'])->toBeTrue();
    expect($result['data']['status'])->toBe('successful');
});

it('gets direct charge details through transaction endpoint', function () {
    $paychangu = Mockery::mock(Paychangu::class);
    $paychangu->shouldReceive('get_direct_charge_details')
        ->with('charge-789')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => ['status' => 'completed', 'amount' => 100],
        ]);

    $gateway = new PayChanguGateway($paychangu);

    $result = $gateway->verifyTransaction('charge-789', 'bank_transfer');

    expect($result['success'])->toBeTrue();
    expect($result['data']['status'])->toBe('completed');
});

it('verifies webhook signature', function () {
    config(['paychangu.secret' => 'test-secret']);

    $payload = '{"event_type":"api.charge.payment","status":"success"}';
    $computed = hash_hmac('sha256', $payload, 'test-secret');

    $paychangu = Mockery::mock(Paychangu::class);
    $gateway = new PayChanguGateway($paychangu);

    expect($gateway->verifyWebhookSignature($computed, $payload))->toBeTrue();
});

it('rejects invalid webhook signature', function () {
    config(['paychangu.secret' => 'test-secret']);

    $payload = '{"event_type":"api.charge.payment","status":"success"}';

    $paychangu = Mockery::mock(Paychangu::class);
    $gateway = new PayChanguGateway($paychangu);

    expect($gateway->verifyWebhookSignature('invalid-signature', $payload))->toBeFalse();
});

it('rejects webhook signature when secret is empty', function () {
    config(['paychangu.secret' => '']);

    $payload = '{"event_type":"api.charge.payment"}';

    $paychangu = Mockery::mock(Paychangu::class);
    $gateway = new PayChanguGateway($paychangu);

    expect($gateway->verifyWebhookSignature('any-sig', $payload))->toBeFalse();
});
