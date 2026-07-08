<?php

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\User;
use App\Models\Verification;

it('shares the latest verification outcome with the identity page', function () {
    $user = User::factory()->create();
    Verification::create([
        'user_id' => $user->id,
        'type' => VerificationType::NationalId,
        'document_ref' => 'ID-123',
        'status' => VerificationStatus::Rejected,
        'admin_notes' => 'The document image was not readable.',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('verify.identity'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('verify/identity')
            ->where('latestVerification.status', 'rejected')
            ->where('latestVerification.admin_notes', 'The document image was not readable.'));
});
