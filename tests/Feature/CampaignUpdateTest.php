<?php

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
});

test('owner can post an update to their campaign', function () {
    $this->actingAs($this->owner);

    $response = $this->post(route('campaigns.updates.store', $this->campaign), [
        'title' => 'We reached our goal!',
        'body' => 'Thanks to everyone who donated. We hit 100% of our target!',
    ]);

    $response->assertRedirect();
    expect(CampaignUpdate::where('campaign_id', $this->campaign->id)->count())->toBe(1);
    expect(CampaignUpdate::first()->title)->toBe('We reached our goal!');
});

test('non-owner cannot post an update', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $response = $this->post(route('campaigns.updates.store', $this->campaign), [
        'title' => 'Unauthorized update',
        'body' => 'This should not be allowed.',
    ]);

    $response->assertForbidden();
});

test('guest cannot post an update', function () {
    $response = $this->post(route('campaigns.updates.store', $this->campaign), [
        'title' => 'Guest update',
        'body' => 'This should not be allowed.',
    ]);

    $response->assertRedirect(route('login'));
});

test('update title is required', function () {
    $this->actingAs($this->owner);

    $response = $this->post(route('campaigns.updates.store', $this->campaign), [
        'title' => '',
        'body' => 'Some update body.',
    ]);

    $response->assertSessionHasErrors('title');
});

test('update body is required', function () {
    $this->actingAs($this->owner);

    $response = $this->post(route('campaigns.updates.store', $this->campaign), [
        'title' => 'Update title',
        'body' => '',
    ]);

    $response->assertSessionHasErrors('body');
});
