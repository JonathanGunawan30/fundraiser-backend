<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        if (app()->bound('log')) {
            app('log')->pushProcessor(new \App\Logging\Processors\MaskSensitiveDataProcessor());
        }

        // Allow public access to API documentation in all environments
        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });
    }
}
