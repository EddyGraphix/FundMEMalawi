<?php

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\User;
use App\Models\Verification;

it('records review timestamps and reviewer identity for verification decisions', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('review verifications');

    $verification = Verification::create([
        'user_id' => $admin->id,
        'type' => VerificationType::NationalId,
        'document_ref' => 'ID-AUDIT-1',
        'status' => VerificationStatus::Pending,
    ]);

    $this->actingAs($admin);

    $this->post(route('admin.verifications.approve', $verification));

    $verification->refresh();

    expect($verification->status)->toBe(VerificationStatus::Approved)
        ->and($verification->reviewed_by)->toBe($admin->id)
        ->and($verification->reviewed_at)->not->toBeNull();

    $rejectedVerification = Verification::create([
        'user_id' => $admin->id,
        'type' => VerificationType::NationalId,
        'document_ref' => 'ID-AUDIT-2',
        'status' => VerificationStatus::Pending,
    ]);

    $this->post(route('admin.verifications.reject', $rejectedVerification), [
        'notes' => 'The document image was too blurry.',
    ]);

    $rejectedVerification->refresh();

    expect($rejectedVerification->status)->toBe(VerificationStatus::Rejected)
        ->and($rejectedVerification->reviewed_by)->toBe($admin->id)
        ->and($rejectedVerification->reviewed_at)->not->toBeNull();
});
