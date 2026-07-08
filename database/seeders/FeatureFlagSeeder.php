<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'ai_assistant', 'name' => 'AI Campaign Assistant', 'description' => 'Enable AI-powered campaign title, story, and CTA generation', 'enabled' => false],
            ['key' => 'stripe_payments', 'name' => 'Stripe Payments', 'description' => 'Enable Stripe payment integration', 'enabled' => false],
            ['key' => 'mpamba_payments', 'name' => 'Mpamba Payments', 'description' => 'Enable TNM Mpamba mobile money payments', 'enabled' => true],
            ['key' => 'airtel_money_payments', 'name' => 'Airtel Money Payments', 'description' => 'Enable Airtel Money payments', 'enabled' => true],
            ['key' => 'recurring_donations', 'name' => 'Recurring Donations', 'description' => 'Enable monthly/weekly recurring donation options', 'enabled' => false],
            ['key' => 'team_fundraising', 'name' => 'Team Fundraising', 'description' => 'Allow multiple organizers on a campaign', 'enabled' => false],
            ['key' => 'ussd_payments', 'name' => 'USSD Payments', 'description' => 'Enable USSD payment codes', 'enabled' => false],
            ['key' => 'campaign_templates', 'name' => 'Campaign Templates', 'description' => 'Show template picker when creating a campaign', 'enabled' => false],
            ['key' => 'draft_auto_save', 'name' => 'Draft Auto-Save', 'description' => 'Auto-save campaign drafts during creation', 'enabled' => false],
            ['key' => 'campaign_health_score', 'name' => 'Campaign Health Score', 'description' => 'Show campaign quality scoring engine', 'enabled' => false],
            ['key' => 'organizer_profiles', 'name' => 'Organizer Profiles', 'description' => 'Enable public organizer portfolio pages', 'enabled' => false],
            ['key' => 'donor_profiles', 'name' => 'Donor Profiles', 'description' => 'Enable public donor profile pages', 'enabled' => false],
            ['key' => 'organization_accounts', 'name' => 'Organization Accounts', 'description' => 'Allow organization and church profiles', 'enabled' => false],
            ['key' => 'gamification', 'name' => 'Gamification', 'description' => 'Enable achievement badges and rewards', 'enabled' => false],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::firstOrCreate(
                ['key' => $flag['key']],
                $flag,
            );
        }
    }
}
