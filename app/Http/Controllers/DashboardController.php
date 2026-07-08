<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $payload = $this->buildOrganizerPayload($user);

        $totalDonated = Donation::where(function ($q) use ($user) {
            $q->where('donor_id', $user->id)
                ->orWhere('claimed_by_id', $user->id);
        })
            ->where('status', DonationStatus::Completed)
            ->sum('amount');

        $recentDonations = Donation::with('campaign:id,title,slug')
            ->where(function ($q) use ($user) {
                $q->where('donor_id', $user->id)
                    ->orWhere('claimed_by_id', $user->id);
            })
            ->latest()
            ->take(5)
            ->get(['id', 'campaign_id', 'amount', 'status', 'created_at', 'donor_name']);

        return Inertia::render('dashboard/index', [
            'campaigns' => $payload['campaigns'],
            'totalRaised' => $payload['totalRaised'],
            'totalWithdrawn' => $payload['totalWithdrawn'],
            'activeCampaignsCount' => $payload['activeCampaignsCount'],
            'pendingCampaignsCount' => $payload['pendingCampaignsCount'],
            'completedDonationsCount' => $payload['completedDonationsCount'],
            'averageDonationAmount' => $payload['averageDonationAmount'],
            'completionRate' => $payload['completionRate'],
            'totalDonated' => $totalDonated,
            'recentDonations' => $recentDonations,
        ]);
    }

    public function myCampaigns()
    {
        $user = Auth::user();
        $payload = $this->buildOrganizerPayload($user);

        $recentDonations = Donation::with('campaign:id,title,slug')
            ->whereIn('campaign_id', $payload['campaigns']->pluck('id'))
            ->latest()
            ->take(5)
            ->get(['id', 'campaign_id', 'amount', 'status', 'created_at', 'donor_name']);

        return Inertia::render('dashboard/organizer', [
            'campaigns' => $payload['campaigns'],
            'totalRaised' => $payload['totalRaised'],
            'totalWithdrawn' => $payload['totalWithdrawn'],
            'activeCampaignsCount' => $payload['activeCampaignsCount'],
            'pendingCampaignsCount' => $payload['pendingCampaignsCount'],
            'completedDonationsCount' => $payload['completedDonationsCount'],
            'averageDonationAmount' => $payload['averageDonationAmount'],
            'completionRate' => $payload['completionRate'],
            'recentDonations' => $recentDonations,
        ]);
    }

    protected function buildOrganizerPayload($user): array
    {
        $campaigns = Campaign::where('owner_id', $user->id)
            ->withCount(['donations', 'donations as completed_donations_count' => fn ($q) => $q->where('status', 'completed')])
            ->latest()
            ->get();

        $completedDonations = Donation::whereIn('campaign_id', $campaigns->pluck('id'))
            ->where('status', DonationStatus::Completed);
        $allDonations = Donation::whereIn('campaign_id', $campaigns->pluck('id'));

        $totalRaised = $completedDonations->sum('amount');
        $completedDonationsCount = $completedDonations->count();
        $averageDonationAmount = $completedDonationsCount > 0 ? round($totalRaised / $completedDonationsCount, 2) : 0;
        $completionRate = $allDonations->count() > 0 ? round(($completedDonationsCount / $allDonations->count()) * 100) : 0;
        $totalWithdrawn = $campaigns->sum(fn ($c) => $c->withdrawals()->where('status', 'paid')->sum('amount'));
        $pendingCampaignsCount = $campaigns->where('status', CampaignStatus::PendingReview)->count();

        return [
            'campaigns' => $campaigns,
            'totalRaised' => $totalRaised,
            'totalWithdrawn' => $totalWithdrawn,
            'activeCampaignsCount' => $campaigns->where('status', CampaignStatus::Active)->count(),
            'pendingCampaignsCount' => $pendingCampaignsCount,
            'completedDonationsCount' => $completedDonationsCount,
            'averageDonationAmount' => $averageDonationAmount,
            'completionRate' => $completionRate,
        ];
    }

    public function donations()
    {
        $donations = Donation::with('campaign')
            ->where(function ($q) {
                $q->where('donor_id', Auth::id())
                    ->orWhere('claimed_by_id', Auth::id());
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('dashboard/donor', [
            'donations' => $donations,
        ]);
    }
}
