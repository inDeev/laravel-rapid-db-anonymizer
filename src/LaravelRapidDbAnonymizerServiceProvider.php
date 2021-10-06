<?php

namespace Indeev\LaravelRapidDbAnonymizer;

use Illuminate\Support\ServiceProvider;

class LaravelRapidDbAnonymizerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-rapid-db-anonymizer.php' => config_path('laravel-rapid-db-anonymizer.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([
                \Indeev\LaravelRapidDbAnonymizer\Console\Commands\LaravelRapidDbAnonymizer::class
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-rapid-db-anonymizer.php', 'laravel-rapid-db-anonymizer');
    }
}
