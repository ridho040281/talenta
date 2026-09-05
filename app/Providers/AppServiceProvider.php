<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce application timezone and Indonesian locale globally
        $timezone = config('app.timezone', 'Asia/Jakarta');
        date_default_timezone_set($timezone);
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));

        // Share App Settings globally across all views with intelligent caching
        View::composer('*', function ($view) {
            $view->with('appSettings', AppSetting::allKeyValues());
        });
    }
}
