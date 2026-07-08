<?php

use App\Models\Campaign;
use App\Models\Comment;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->campaign = Campaign::factory()->active()->create(['owner_id' => $this->owner->id]);
});

test('authenticated user can post a comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('campaigns.comments.store', $this->campaign), [
        'body' => 'Great campaign! Keep it up.',
    ]);

    $response->assertRedirect();
    expect(Comment::where('campaign_id', $this->campaign->id)->count())->toBe(1);
    expect(Comment::first()->body)->toBe('Great campaign! Keep it up.');
});

test('guest cannot post a comment', function () {
    $response = $this->post(route('campaigns.comments.store', $this->campaign), [
        'body' => 'Nice work!',
    ]);

    $response->assertRedirect(route('login'));
});

test('comment body is required', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('campaigns.comments.store', $this->campaign), [
        'body' => '',
    ]);

    $response->assertSessionHasErrors('body');
});

test('comment is linked to the authenticated user', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $this->actingAs($user);

    $this->post(route('campaigns.comments.store', $this->campaign), [
        'body' => 'Supporting this cause!',
    ]);

    $comment = Comment::first();
    expect($comment->user_id)->toBe($user->id);
    expect($comment->user->name)->toBe('Jane Doe');
});
