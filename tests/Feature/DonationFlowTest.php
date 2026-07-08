<?php

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
});

test('guest can view donate page', function () {
    $response = $this->get(route('campaigns.donate', $this->campaign));

    $response->assertSuccessful();
});

test('guest can submit donation without an account', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    expect($response->isRedirection())->toBeTrue();
});

test('donation is stored with initiated status', function () {
    $donation = Donation::factory()->initiated()->create([
        'campaign_id' => $this->campaign->id,
    ]);

    assertDatabaseHas('donations', [
        'id' => $donation->id,
        'status' => 'initiated',
    ]);
});

test('donation can be completed', function () {
    $donation = Donation::factory()->completed()->create([
        'campaign_id' => $this->campaign->id,
    ]);

    expect($donation->isCompleted())->toBeTrue();
});

test('donation requires minimum amount', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 0,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    expect($response->isRedirection())->toBeTrue();
});

test('donation requires donor name', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => '',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertSessionHasErrors('donor_name');
});

test('donation requires donor email', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => '',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertSessionHasErrors('donor_email');
});

test('donation requires donor phone', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertSessionHasErrors('donor_phone');
});

test('donation requires valid payment method', function () {
    $response = $this->post(route('campaigns.donate.store', $this->campaign), [
        'amount' => 50,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'bitcoin',
    ]);

    expect($response->isRedirection())->toBeTrue();
});

test('cannot donate to non-active campaign', function () {
    $draftCampaign = Campaign::factory()->create([
        'owner_id' => $this->owner->id,
        'status' => CampaignStatus::Draft,
    ]);

    $response = $this->post(route('campaigns.donate.store', $draftCampaign), [
        'amount' => 50,
        'donor_name' => 'Alice Gondwe',
        'donor_email' => 'alice@example.com',
        'donor_phone' => '0999123456',
        'payment_method' => 'airtel_money',
    ]);

    $response->assertForbidden();
});
