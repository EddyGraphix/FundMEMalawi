<?php

namespace Database\Seeders;

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CategorySeeder::class,
            FeatureFlagSeeder::class,
            PlatformSettingSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@fundme.mw'],
            [
                'name' => 'Admin User',
                'phone' => '0999000001',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $verified = User::firstOrCreate(
            ['email' => 'verified@fundme.mw'],
            [
                'name' => 'Verified User',
                'phone' => '0999000002',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        Verification::firstOrCreate(
            ['user_id' => $verified->id, 'type' => VerificationType::NationalId],
            [
                'document_ref' => 'MW-ID-987654',
                'status' => VerificationStatus::Approved,
                'admin_notes' => 'Verified via seeder',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@fundme.mw'],
            [
                'name' => 'Regular User',
                'phone' => '0999000003',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
