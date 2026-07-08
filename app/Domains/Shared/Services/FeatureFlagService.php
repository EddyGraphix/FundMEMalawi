<?php

namespace App\Domains\Shared\Services;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    public function isEnabled(string $key): bool
    {
        return Cache::remember("feature.{$key}", 300, function () use ($key) {
            return FeatureFlag::isEnabled($key);
        });
    }

    public function enable(string $key): void
    {
        FeatureFlag::where('key', $key)->update(['enabled' => true]);
        Cache::forget("feature.{$key}");
    }

    public function disable(string $key): void
    {
        FeatureFlag::where('key', $key)->update(['enabled' => false]);
        Cache::forget("feature.{$key}");
    }

    public function all(): array
    {
        return FeatureFlag::all()->keyBy('key')->toArray();
    }
}
