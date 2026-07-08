<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PlatformSettingController extends Controller
{
    public function index()
    {
        Gate::authorize('manage platform settings');

        PlatformSetting::ensureDefaults();

        $settings = PlatformSetting::query()->get()->groupBy('group');

        return Inertia::render('admin/platform-settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        Gate::authorize('manage platform settings');

        $settings = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'exists:platform_settings,key'],
            'settings.*.value' => ['nullable', 'string'],
        ]);

        foreach ($settings['settings'] as $setting) {
            PlatformSetting::where('key', $setting['key'])->update([
                'value' => $setting['value'] ?? '',
            ]);

            Cache::forget("settings.{$setting['key']}");
        }

        return back()->with('flash', ['success' => 'Settings updated.']);
    }
}
