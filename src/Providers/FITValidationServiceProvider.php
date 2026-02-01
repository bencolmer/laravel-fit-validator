<?php

namespace BenColmer\LaravelFITValidation\Providers;

use Illuminate\Support\ServiceProvider;

class FITValidationServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fit.php' => $this->app->configPath('fit.php'),
            ], 'config');
        }
    }
}
