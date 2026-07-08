<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'slug' => Str::slug(fake()->sentence(4)).'-'.Str::random(6),
            'story' => fake()->paragraphs(3, true),
            'goal_amount' => fake()->numberBetween(5000, 1000000),
            'raised_amount' => 0,
            'currency' => 'MWK',
            'status' => CampaignStatus::PendingReview,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => CampaignStatus::Active]);
    }

    public function pendingReview(): static
    {
        return $this->state(['status' => CampaignStatus::PendingReview]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => CampaignStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
            'rejected_at' => now(),
        ]);
    }
}
