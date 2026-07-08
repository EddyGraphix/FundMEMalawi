<?php

namespace App\Domains\Shared\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("settings.{$key}", 3600, function () use ($key, $default) {
            return PlatformSetting::getValue($key, $default);
        });
    }

    public function set(string $key, mixed $value, ?string $type = null): void
    {
        PlatformSetting::setValue($key, $value, $type);
        Cache::forget("settings.{$key}");
    }

    public function all(?string $group = null): array
    {
        $query = PlatformSetting::query();
        if ($group) {
            $query->where('group', $group);
        }

        return $query->get()->keyBy('key')->toArray();
    }
}
