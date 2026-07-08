<?php

namespace App\Http\Controllers;

use App\Models\Campaign;

class CampaignFollowController extends Controller
{
    public function store(Campaign $campaign)
    {
        request()->user()->followedCampaigns()->firstOrCreate(['campaign_id' => $campaign->id]);

        return back()->with('flash', ['success' => 'Now following this campaign.']);
    }

    public function destroy(Campaign $campaign)
    {
        request()->user()->followedCampaigns()->where('campaign_id', $campaign->id)->delete();

        return back()->with('flash', ['success' => 'Unfollowed campaign.']);
    }
}
