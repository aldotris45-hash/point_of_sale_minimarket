<?php

namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Settings\SettingsService;
use App\Services\Settings\SettingsServiceInterface;
use App\Services\Category\CategoryServiceInterface;
use App\Services\Category\CategoryService;
use App\Services\Product\ProductServiceInterface;
use App\Services\Product\ProductService;
use App\Services\User\UserServiceInterface;
use App\Services\User\UserService;
use App\Services\Cashier\CashierServiceInterface;
use App\Services\Cashier\CashierService;
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
        $this->app->singleton(CategoryServiceInterface::class, CategoryService::class);
        $this->app->singleton(ProductServiceInterface::class, ProductService::class);
        $this->app->singleton(UserServiceInterface::class, UserService::class);
        $this->app->singleton(CashierServiceInterface::class, CashierService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $settings = $this->app->make(SettingsServiceInterface::class);
        View::share('appStoreName', $settings->storeName());
        View::share('appCurrency', $settings->currency());
        View::share('appDiscountPercent', $settings->discountPercent());
        View::share('appTaxPercent', $settings->taxPercent());
        View::share('appStoreAddress', $settings->storeAddress());
        View::share('appStorePhone', $settings->storePhone());
        View::share('appStoreLogoPath', $settings->storeLogoPath());
        View::share('appReceiptFormat', $settings->receiptNumberFormat());
    }
}
