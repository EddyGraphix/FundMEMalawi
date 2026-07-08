<?php

namespace App\Http\Controllers;

use App\Models\User;

class OrganizerPortfolioController extends Controller
{
    public function show(User $user)
    {
        $campaigns = $user->campaigns()->with('category')->latest()->paginate(12);

        return inertia('organizers/show', [
            'organizer' => $user->load('media'),
            'campaigns' => $campaigns,
        ]);
    }
}
