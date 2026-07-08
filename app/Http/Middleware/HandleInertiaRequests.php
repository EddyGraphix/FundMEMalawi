<?php

namespace App\Http\Middleware;

use App\Enums\VerificationType;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $data = [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'roles' => $user->getRoleNames(),
                    'verification_status' => $user->verifications()
                        ->where('type', VerificationType::NationalId)
                        ->latest()
                        ->value('status'),
                    'unclaimed_donations' => $user->email
                        ? Donation::whereNull('claimed_by_id')
                            ->where('donor_email', $user->email)
                            ->count()
                        : 0,
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];

        $legacyFlash = $request->session()->get('flash');
        if (is_array($legacyFlash)) {
            $toast = match (true) {
                isset($legacyFlash['success']) => ['type' => 'success', 'message' => $legacyFlash['success']],
                isset($legacyFlash['error']) => ['type' => 'error', 'message' => $legacyFlash['error']],
                isset($legacyFlash['warning']) => ['type' => 'warning', 'message' => $legacyFlash['warning']],
                isset($legacyFlash['info']) => ['type' => 'info', 'message' => $legacyFlash['info']],
                default => null,
            };

            if ($toast) {
                $data['flash']['toast'] = $toast;
            }
        }

        return $data;
    }
}
