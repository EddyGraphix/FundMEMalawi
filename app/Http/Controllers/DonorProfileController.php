<?php

namespace App\Http\Controllers;

use App\Models\User;

class DonorProfileController extends Controller
{
    public function show(User $user)
    {
        $donations = $user->donations()->with('campaign')->latest()->paginate(20);

        return inertia('donors/show', [
            'donor' => $user,
            'donations' => $donations,
        ]);
    }
}
