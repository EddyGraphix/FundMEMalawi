<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->word().' '.uniqid();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
