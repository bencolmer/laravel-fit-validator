<?php

namespace BenColmer\LaravelFITValidator\Providers;

use BenColmer\LaravelFITValidator\Contracts\FITKeySetClient as FITKeySetClientContract;
use BenColmer\LaravelFITValidator\Contracts\FITValidator as FITValidatorContract;
use BenColmer\LaravelFITValidator\FITKeySetClient;
use BenColmer\LaravelFITValidator\FITValidator;
use BenColmer\LaravelFITValidator\Http\Middleware\ValidateFIT;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FITKeySetClientContract::class, FITKeySetClient::class);
        $this->app->bind(FITValidatorContract::class, FITValidator::class);
    }

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
        $alias = Config::get('fit.middlewareAlias', 'fit');
        $this->app->make('router')->aliasMiddleware($alias, ValidateFIT::class);
    }
}
