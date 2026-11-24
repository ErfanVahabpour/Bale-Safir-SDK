<?php

namespace EFive\Bale\Safir\Laravel;

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\SafirManager;
use Illuminate\Support\ServiceProvider;

final class SafirServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/safir.php', 'bale-safir');

        $this->app->singleton(SafirManager::class, static function ($app): SafirManager {
            return new SafirManager((array) $app['config']->get('bale-safir', []));
        });

        $this->app->alias(SafirManager::class, 'bale-safir');
        $this->app->bind(Api::class, static fn ($app): Api => $app->make(SafirManager::class)->client());
        $this->app->alias(Api::class, 'bale-safir.client');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/safir.php' => config_path('bale-safir.php'),
            ], 'bale-safir-config');
        }
    }
}
