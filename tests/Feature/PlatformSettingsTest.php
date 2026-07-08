<?php

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

it('backs up default platform settings when none exist', function () {
    PlatformSetting::query()->delete();
    Cache::flush();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('admin.settings'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/platform-settings')
            ->where('settings.general.0.key', 'maintenance_mode'));
});

it('persists updated platform settings and clears cached values', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $setting = PlatformSetting::create([
        'key' => 'maintenance_mode',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'general',
        'label' => 'Maintenance Mode',
    ]);

    Cache::put("settings.{$setting->key}", true, 3600);

    $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
        'settings' => [
            ['key' => $setting->key, 'value' => 'true'],
        ],
    ]);

    $response->assertRedirect();

    $setting->refresh();

    expect($setting->value)->toBe('true')
        ->and(Cache::has("settings.{$setting->key}"))->toBeFalse();
});
