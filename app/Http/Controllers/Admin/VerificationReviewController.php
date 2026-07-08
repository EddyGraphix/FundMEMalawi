<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VerificationReviewController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('review verifications');

        $statusFilter = $request->input('status');
        $query = Verification::with('user')
            ->latest();

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        $verifications = $query->paginate(20)->appends($request->only('status'));

        $stats = [
            'pending' => Verification::where('status', 'pending')->count(),
            'approved' => Verification::where('status', 'approved')->count(),
            'rejected' => Verification::where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/verifications', [
            'verifications' => $verifications,
            'stats' => $stats,
            'statusFilter' => $statusFilter ?? 'all',
        ]);
    }

    public function approve(Verification $verification)
    {
        Gate::authorize('review verifications');

        $verification->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => null,
        ]);

        return back()->with('flash', ['success' => 'Verification approved.']);
    }

    public function reject(Request $request, Verification $verification)
    {
        Gate::authorize('review verifications');

        $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $verification->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->notes,
        ]);

        return back()->with('flash', ['success' => 'Verification rejected.']);
    }
}
