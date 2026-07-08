<?php

namespace App\Providers;

use App\Domains\Shared\Services\FeatureFlagService;
use App\Domains\Shared\Services\SettingsService;
use App\Services\TimedPayChangu;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Paychangu\Laravel\Paychangu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(FeatureFlagService::class);

        $this->app->singleton(Paychangu::class, fn () => new TimedPayChangu);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::define('viewPulse', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage withdrawals', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage categories', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage platform settings', fn ($user) => $user->hasRole('admin'));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
