<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignUpdateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
        ];
    }
}
