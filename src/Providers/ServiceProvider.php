<?php

namespace BenColmer\LaravelFITValidator\Providers;

use BenColmer\LaravelFITValidator\Http\Middleware\ValidateFIT;
use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerConfig();

        $this->registerMiddleware();
    }

    protected function registerConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/fit.php' => $this->app->configPath('fit.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__ . '/../../config/fit.php', 'fit');
    }

    protected function registerMiddleware(): void
    {
        $this->app->make('router')->aliasMiddleware('fit', ValidateFIT::class);
    }
}
