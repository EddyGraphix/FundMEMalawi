<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Campaign $campaign)
    {
        Gate::authorize('view', $campaign);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $campaign->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('flash', ['success' => 'Comment posted.']);
    }
}
