<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Medical & Health', 'slug' => 'medical-health'],
            ['name' => 'Education & Scholarships', 'slug' => 'education-scholarships'],
            ['name' => 'Community Development', 'slug' => 'community-development'],
            ['name' => 'Agriculture & Farming', 'slug' => 'agriculture-farming'],
            ['name' => 'Emergency & Disaster Relief', 'slug' => 'emergency-disaster-relief'],
            ['name' => 'Small Business & Entrepreneurship', 'slug' => 'small-business'],
            ['name' => 'Arts & Culture', 'slug' => 'arts-culture'],
            ['name' => 'Religious & Church', 'slug' => 'religious-church'],
            ['name' => 'Sports & Recreation', 'slug' => 'sports-recreation'],
            ['name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate($category);
        }
    }
}
