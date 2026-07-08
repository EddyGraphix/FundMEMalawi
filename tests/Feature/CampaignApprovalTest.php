<?php

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create()->assignRole('admin');
});

test('admin can view campaign approvals page', function () {
    $this->actingAs($this->admin);
    $response = $this->get(route('admin.campaigns'));

    $response->assertSuccessful();
});

test('non-admin cannot view campaign approvals page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.campaigns'));
    $response->assertForbidden();
});

test('guest cannot view campaign approvals page', function () {
    $response = $this->get(route('admin.campaigns'));
    $response->assertRedirect(route('login'));
});

test('admin can approve a pending campaign', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->pendingReview()->create();

    $response = $this->post(route('admin.campaigns.approve', $campaign));

    $response->assertRedirect();
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Active);
});

test('admin can reject a pending campaign with reason', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->pendingReview()->create();

    $response = $this->post(route('admin.campaigns.reject', $campaign), [
        'reason' => 'Does not meet our guidelines for medical fundraisers.',
    ]);

    $response->assertRedirect();
    $refreshed = $campaign->fresh();
    expect($refreshed->status)->toBe(CampaignStatus::Rejected);
    expect($refreshed->rejection_reason)->toBe('Does not meet our guidelines for medical fundraisers.');
    expect($refreshed->rejected_at)->not->toBeNull();
});

test('admin cannot approve already active campaign', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->active()->create();

    $response = $this->post(route('admin.campaigns.approve', $campaign));

    $response->assertStatus(422);
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Active);
});

test('admin cannot reject already active campaign', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->active()->create();

    $response = $this->post(route('admin.campaigns.reject', $campaign), [
        'reason' => 'Invalid state',
    ]);

    $response->assertStatus(422);
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Active);
});

test('rejection requires a reason', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->pendingReview()->create();

    $response = $this->post(route('admin.campaigns.reject', $campaign), [
        'reason' => '',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('reason');
});

test('approvals page only shows pending and flagged campaigns', function () {
    $this->actingAs($this->admin);

    Campaign::factory()->pendingReview()->create(['title' => 'Pending One']);
    Campaign::factory()->pendingReview()->create(['title' => 'Pending Two']);
    Campaign::factory()->active()->create(['title' => 'Active One']);
    Campaign::factory()->rejected()->create(['title' => 'Rejected One']);

    $response = $this->get(route('admin.campaigns'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/campaign-approvals')
        ->has('campaigns.data', 2)
    );
});
