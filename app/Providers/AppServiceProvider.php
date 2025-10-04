<?php

namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Settings\SettingsService;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->singleton(SettingsServiceInterface::class, SettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share basic settings with all views
        $settings = $this->app->make(SettingsServiceInterface::class);
        View::share('appStoreName', $settings->storeName());
        View::share('appCurrency', $settings->currency());
        View::share('appDiscountPercent', $settings->discountPercent());
        View::share('appTaxPercent', $settings->taxPercent());
    }
}
