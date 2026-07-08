<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Donation;
use App\Models\User;
use App\Services\BeneficiaryInviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function __construct(
        protected BeneficiaryInviteService $beneficiaryInvite
    ) {}

    public function index(Request $request)
    {
        $campaigns = Campaign::with(['owner', 'category', 'media'])
            ->where('status', CampaignStatus::Active)
            ->withCount(['donations as donor_count' => fn ($q) => $q->where('status', DonationStatus::Completed->value)])
            ->latest()
            ->paginate(12);

        return Inertia::render('campaigns/index', [
            'campaigns' => $campaigns,
            'categories' => Category::all(),
        ]);
    }

    public function show(Campaign $campaign)
    {
        Gate::authorize('view', $campaign);

        $campaign->load([
            'owner',
            'beneficiary',
            'category',
            'media',
            'updates' => fn ($q) => $q->latest(),
            'donations' => fn ($q) => $q->where('status', DonationStatus::Completed->value)->latest()->limit(50),
            'comments.user',
        ]);

        return Inertia::render('campaigns/show', [
            'campaign' => $campaign,
            'donations' => $campaign->donations,
        ]);
    }

    public function create()
    {
        $response = Gate::inspect('create', Campaign::class);

        if ($response->denied()) {
            return redirect()->route('verify.identity')
                ->with('flash', ['warning' => 'Please verify your identity before creating a campaign.']);
        }

        return Inertia::render('campaigns/create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $response = Gate::inspect('create', Campaign::class);

        if ($response->denied()) {
            return redirect()->route('verify.identity')
                ->with('flash', ['warning' => 'Please verify your identity before creating a campaign.']);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'story' => ['required', 'string'],
            'goal_amount' => ['required', 'numeric', 'min:1.00'],
            'beneficiary_email' => ['nullable', 'email', 'exists:users,email'],
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(6);
        $validated['owner_id'] = Auth::id();
        $validated['status'] = CampaignStatus::PendingReview;

        if ($request->filled('beneficiary_email')) {
            $beneficiary = User::where('email', $request->beneficiary_email)->first();
            $validated['beneficiary_id'] = $beneficiary->id;
        }

        $campaign = Campaign::create($validated);

        if ($request->hasFile('cover_image')) {
            $campaign->addMediaFromRequest('cover_image')->toMediaCollection('cover');
            $campaign->update(['cover_media' => $campaign->getFirstMediaUrl('cover')]);
        }

        return redirect()->route('campaigns.show', $campaign)
            ->with('flash', ['success' => 'Campaign submitted for review.']);
    }

    public function acceptBeneficiaryInvite(Campaign $campaign)
    {
        $user = Auth::user();

        $campaign->update(['beneficiary_id' => $user->id]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('flash', ['success' => 'You are now the beneficiary for this campaign. Complete your verification to receive payouts.']);
    }

    public function inviteBeneficiary(Campaign $campaign)
    {
        Gate::authorize('update', $campaign);

        $link = $this->beneficiaryInvite->generateInviteLink($campaign);

        return back()->with('flash', [
            'success' => 'Invite link generated. Share it with the beneficiary.',
            'invite_link' => $link,
        ]);
    }

    public function claimDonations(Request $request)
    {
        $user = $request->user();

        $claimed = Donation::whereNull('claimed_by_id')
            ->where(function ($q) use ($user) {
                $q->where('donor_email', $user->email)
                    ->orWhere('donor_phone', $user->phone);
            })
            ->update([
                'donor_id' => $user->id,
                'claimed_by_id' => $user->id,
                'claimed_at' => now(),
            ]);

        if ($claimed > 0) {
            return back()->with('flash', ['success' => "{$claimed} previous donation(s) linked to your account."]);
        }

        return back()->with('flash', ['info' => 'No previous donations found matching your email or phone.']);
    }
}
