<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CampaignUpdateController extends Controller
{
    public function store(Request $request, Campaign $campaign)
    {
        Gate::authorize('update', $campaign);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $campaign->updates()->create($validated);

        return back()->with('flash', ['success' => 'Update posted.']);
    }
}
