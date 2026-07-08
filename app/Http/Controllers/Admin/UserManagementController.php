<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        Gate::authorize('manage users');

        $users = User::latest()->paginate(20);

        return Inertia::render('admin/users', [
            'users' => $users,
        ]);
    }
}
