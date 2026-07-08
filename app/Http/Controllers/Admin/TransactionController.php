<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view revenue reports');

        $query = Donation::with(['campaign', 'donor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $donations = $query->latest()->paginate(20);

        $totals = [
            'total_donations' => Donation::where('status', 'completed')->sum('amount'),
            'total_fees' => Donation::where('status', 'completed')->sum('tip_amount'),
            'total_count' => Donation::where('status', 'completed')->count(),
        ];

        return Inertia::render('admin/transactions', [
            'donations' => $donations,
            'totals' => $totals,
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }
}
