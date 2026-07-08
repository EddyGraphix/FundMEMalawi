<?php

use App\Http\Controllers\Admin\CampaignApprovalController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationReviewController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignFollowController;
use App\Http\Controllers\CampaignUpdateController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonorProfileController;
use App\Http\Controllers\IdentityVerificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizerPortfolioController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavedCampaignController;
use App\Http\Controllers\TrustCenterController;
use App\Http\Controllers\WebhookController;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('trust')->name('trust.')->group(function () {
    Route::get('/', [TrustCenterController::class, 'index'])->name('index');
    Route::get('/fraud-prevention', [TrustCenterController::class, 'fraudPrevention'])->name('fraud-prevention');
    Route::get('/campaign-verification', [TrustCenterController::class, 'campaignVerification'])->name('campaign-verification');
    Route::get('/donor-protection', [TrustCenterController::class, 'donorProtection'])->name('donor-protection');
    Route::get('/refund-policy', [TrustCenterController::class, 'refundPolicy'])->name('refund-policy');
    Route::get('/safety-guidelines', [TrustCenterController::class, 'safetyGuidelines'])->name('safety-guidelines');
    Route::get('/report', [TrustCenterController::class, 'report'])->name('report');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, array_keys(config('app.available_locales', [])))) {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }

    return redirect()->back();
})->name('locale.switch');

Route::inertia('/', 'welcome')->name('home');

Route::prefix('campaigns')->name('campaigns.')->group(function () {
    Route::get('/', [CampaignController::class, 'index'])->name('index');
    Route::get('/create', [CampaignController::class, 'create'])->name('create')->middleware(['auth']);
    Route::post('/', [CampaignController::class, 'store'])->name('store')->middleware(['auth']);
    Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');

    Route::post('/{campaign}/invite-beneficiary', [CampaignController::class, 'inviteBeneficiary'])
        ->name('invite-beneficiary')->middleware(['auth']);

    Route::get('/{campaign}/donate', [DonationController::class, 'create'])->name('donate');
    Route::post('/{campaign}/donate', [DonationController::class, 'store'])->name('donate.store');

    Route::post('/{campaign}/updates', [CampaignUpdateController::class, 'store'])
        ->name('updates.store')->middleware(['auth']);

    Route::post('/{campaign}/comments', [CommentController::class, 'store'])
        ->name('comments.store')->middleware(['auth']);

    Route::get('/{campaign}/report', [ReportController::class, 'create'])->name('report');
    Route::post('/{campaign}/report', [ReportController::class, 'store'])->name('report.store');

    Route::post('/{campaign}/save', [SavedCampaignController::class, 'store'])->name('save')->middleware(['auth']);
    Route::delete('/{campaign}/save', [SavedCampaignController::class, 'destroy'])->name('save.destroy')->middleware(['auth']);

    Route::post('/{campaign}/follow', [CampaignFollowController::class, 'store'])->name('follow')->middleware(['auth']);
    Route::delete('/{campaign}/follow', [CampaignFollowController::class, 'destroy'])->name('follow.destroy')->middleware(['auth']);
});

Route::get('/donations/{donation}/status', [DonationController::class, 'status'])->name('donations.status');
Route::post('/donations/{donation}/verify', [DonationController::class, 'verify'])->name('donations.verify');
Route::get('/donations/{donation}/card-return', [DonationController::class, 'cardReturn'])->name('donations.card-return');

Route::middleware(['auth'])->group(function () {
    Route::get('/verify/identity', [IdentityVerificationController::class, 'create'])->name('verify.identity');
    Route::post('/verify/identity', [IdentityVerificationController::class, 'store'])->name('verify.identity.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/campaigns', [DashboardController::class, 'myCampaigns'])->name('dashboard.campaigns');
    Route::get('/dashboard/donations', [DashboardController::class, 'donations'])->name('dashboard.donations');
    Route::get('/dashboard/saved', [SavedCampaignController::class, 'index'])->name('dashboard.saved');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::post('/claim-donations', [CampaignController::class, 'claimDonations'])->name('claim-donations');
});

Route::get('/organizers/{user}', [OrganizerPortfolioController::class, 'show'])->name('organizers.show');
Route::get('/donors/{user}', [DonorProfileController::class, 'show'])->name('donors.show');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('admin/index', [
            'stats' => [
                'total_campaigns' => Campaign::count(),
                'pending_campaigns' => Campaign::where('status', 'pending_review')->count(),
                'total_users' => User::count(),
                'total_donations' => Donation::where('status', 'completed')->count(),
                'total_raised' => Donation::where('status', 'completed')->sum('amount'),
            ],
        ]);
    })->name('dashboard');

    Route::get('/campaigns', [CampaignApprovalController::class, 'index'])->name('campaigns');
    Route::post('/campaigns/{campaign}/approve', [CampaignApprovalController::class, 'approve'])->name('campaigns.approve');
    Route::post('/campaigns/{campaign}/reject', [CampaignApprovalController::class, 'reject'])->name('campaigns.reject');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users');

    Route::get('/verifications', [VerificationReviewController::class, 'index'])->name('verifications');
    Route::post('/verifications/{verification}/approve', [VerificationReviewController::class, 'approve'])->name('verifications.approve');
    Route::post('/verifications/{verification}/reject', [VerificationReviewController::class, 'reject'])->name('verifications.reject');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/settings', [PlatformSettingController::class, 'index'])->name('settings');
    Route::put('/settings', [PlatformSettingController::class, 'update'])->name('settings.update');
});

Route::post('/webhook/paychangu', [WebhookController::class, 'payChangu'])->name('webhook.paychangu');

Route::get('/beneficiary/invite/{campaign}', [CampaignController::class, 'acceptBeneficiaryInvite'])
    ->name('beneficiary.invite')->middleware(['signed', 'auth']);

require __DIR__.'/settings.php';
