<?php

namespace Manusiakemos\Wirecrud;

use Illuminate\Support\ServiceProvider;
use Manusiakemos\Wirecrud\Console\WireCrudConsole;

class WirecrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'wirecrud');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wirecrud');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            // Register the command if we are using the application via the CLI
            if ($this->app->runningInConsole()) {
                $this->commands([
                    WireCrudConsole::class,
                ]);
            }

            // Publish your stub file to the user's application if needed
            $this->publishes([
                __DIR__.'/Console/stubs' => base_path('stubs/vendor/manusiakemos/wirecrud'),
            ], 'wirecrud:stubs');

           $this->publishes([
               __DIR__.'/../config/config.php' => config_path('wirecrud.php'),
           ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/wirecrud'),
            ], 'wirecrud:views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/wirecrud'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/wirecrud'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'wirecrud');

        // Register the main class to use with the facade
        $this->app->singleton('wirecrud', function () {
            return new Wirecrud;
        });
    }
}
