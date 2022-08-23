<?php

namespace MGK\Auth;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use MGK\Auth\Services\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'mgk-auth');

        // Register the main class to use
        $this->app->singleton(Auth::class, function ($app) {
            return new Auth(
                config('mgk-auth.host'),
                config('mgk-auth.credentials.client_id'),
                config('mgk-auth.credentials.client_secret')
            );
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('mgk-auth.php'),
            ], 'config');
        }

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasAbility')) {
                return $user->hasAbility($ability) ?: null;
            }
        });
    }
}
