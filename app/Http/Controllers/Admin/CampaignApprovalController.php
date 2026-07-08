<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CampaignStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CampaignApprovalController extends Controller
{
    public function index()
    {
        Gate::authorize('approve', Campaign::class);

        $campaigns = Campaign::with(['owner', 'category'])
            ->whereIn('status', ['pending_review', 'flagged'])
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/campaign-approvals', [
            'campaigns' => $campaigns,
        ]);
    }

    public function approve(Campaign $campaign)
    {
        Gate::authorize('approve', Campaign::class);

        abort_if(! in_array($campaign->status, [CampaignStatus::PendingReview, CampaignStatus::Flagged]), 422);

        $campaign->update(['status' => CampaignStatus::Active]);

        return back()->with('flash', ['success' => 'Campaign approved and is now live.']);
    }

    public function reject(Request $request, Campaign $campaign)
    {
        Gate::authorize('approve', Campaign::class);

        abort_if(! in_array($campaign->status, [CampaignStatus::PendingReview, CampaignStatus::Flagged]), 422);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $campaign->update([
            'status' => CampaignStatus::Rejected,
            'rejection_reason' => $validated['reason'],
            'rejected_at' => Date::now(),
        ]);

        return back()->with('flash', ['success' => 'Campaign rejected.']);
    }
}
