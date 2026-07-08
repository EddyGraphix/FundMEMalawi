<?php

namespace Database\Factories;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'donor_id' => User::factory(),
            'donor_name' => fake()->name(),
            'donor_email' => fake()->safeEmail(),
            'donor_phone' => fake()->optional()->phoneNumber(),
            'is_anonymous' => fake()->boolean(20),
            'amount' => fake()->numberBetween(10, 100000),
            'tip_amount' => fake()->optional(0.3)->numberBetween(5, 50) ?? 0,
            'currency' => 'MWK',
            'payment_method' => fake()->randomElement(DonationPaymentMethod::cases())->value,
            'status' => DonationStatus::Completed,
            'message' => fake()->optional(0.4)->sentence(),
        ];
    }

    public function initiated(): static
    {
        return $this->state(['status' => DonationStatus::Initiated]);
    }

    public function completed(): static
    {
        return $this->state(['status' => DonationStatus::Completed]);
    }

    public function failed(): static
    {
        return $this->state(['status' => DonationStatus::Failed]);
    }

    public function mobileMoney(): static
    {
        return $this->state([
            'payment_method' => fake()->randomElement(['airtel_money', 'tnm_mpamba']),
        ]);
    }
}
