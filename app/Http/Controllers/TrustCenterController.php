<?php

namespace App\Http\Controllers;

class TrustCenterController extends Controller
{
    public function index()
    {
        return inertia('trust/index');
    }

    public function fraudPrevention()
    {
        return inertia('trust/fraud-prevention');
    }

    public function campaignVerification()
    {
        return inertia('trust/campaign-verification');
    }

    public function donorProtection()
    {
        return inertia('trust/donor-protection');
    }

    public function refundPolicy()
    {
        return inertia('trust/refund-policy');
    }

    public function safetyGuidelines()
    {
        return inertia('trust/safety-guidelines');
    }

    public function report()
    {
        return inertia('trust/report');
    }
}
