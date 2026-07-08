<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create(Campaign $campaign)
    {
        return inertia('campaigns/report', [
            'campaign' => $campaign->load('owner'),
        ]);
    }

    public function store(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:abuse,scam,fraud,inappropriate,other'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        CampaignReport::create([
            'campaign_id' => $campaign->id,
            'reporter_id' => $request->user()?->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('flash', ['success' => 'Report submitted. Our team will review it shortly.']);
    }
}
