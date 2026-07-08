<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function index()
    {
        Gate::authorize('manage withdrawals');

        $withdrawals = Withdrawal::with(['campaign', 'requester'])
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/withdrawals', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function approve(Withdrawal $withdrawal)
    {
        Gate::authorize('manage withdrawals');

        $withdrawal->update([
            'status' => WithdrawalStatus::Approved,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('flash', ['success' => 'Withdrawal approved.']);
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        Gate::authorize('manage withdrawals');

        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $withdrawal->update([
            'status' => WithdrawalStatus::Rejected,
            'approved_by' => auth()->id(),
            'admin_notes' => $validated['reason'],
        ]);

        return back()->with('flash', ['success' => 'Withdrawal rejected.']);
    }
}
