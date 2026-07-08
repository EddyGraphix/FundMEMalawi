<?php

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IdentityVerificationController extends Controller
{
    public function create(Request $request)
    {
        $latestVerification = $request->user()?->verifications()->latest()->first();

        return Inertia::render('verify/identity', [
            'latestVerification' => $latestVerification ? [
                'id' => $latestVerification->id,
                'status' => is_object($latestVerification->status) ? $latestVerification->status->value : $latestVerification->status,
                'admin_notes' => $latestVerification->admin_notes,
                'type' => is_object($latestVerification->type) ? $latestVerification->type->value : $latestVerification->type,
                'created_at' => $latestVerification->created_at?->toISOString(),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:national_id,passport'],
            'document_number' => ['required', 'string', 'max:255'],
            'document_image' => ['required', 'image', 'max:5120'],
        ]);

        $verification = $request->user()->verifications()->create([
            'type' => VerificationType::NationalId,
            'document_ref' => $validated['document_number'],
            'status' => VerificationStatus::Pending,
        ]);

        $verification->addMediaFromRequest('document_image')
            ->toMediaCollection('verification_documents');

        return redirect()->route('dashboard')
            ->with('flash', ['success' => 'Identity verification submitted. We\'ll review it shortly and notify you once the decision is made.']);
    }
}
