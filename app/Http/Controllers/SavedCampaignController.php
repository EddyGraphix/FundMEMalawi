<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class SavedCampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = $request->user()->savedCampaigns()->with('campaign.category', 'campaign.owner')->latest()->paginate(12);

        return inertia('dashboard/saved', ['campaigns' => $campaigns]);
    }

    public function store(Campaign $campaign)
    {
        request()->user()->savedCampaigns()->firstOrCreate(['campaign_id' => $campaign->id]);

        return back()->with('flash', ['success' => 'Campaign saved.']);
    }

    public function destroy(Campaign $campaign)
    {
        request()->user()->savedCampaigns()->where('campaign_id', $campaign->id)->delete();

        return back()->with('flash', ['success' => 'Campaign removed from saved.']);
    }
}
