<?php

namespace Ymlluo\GroupRobot;

use Illuminate\Support\ServiceProvider;

class GroupRobotServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ymlluo');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'ymlluo');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/grouprobot.php', 'grouprobot');

        // Register the service the package provides.
        $this->app->singleton('grouprobot', function ($app) {
            return new GroupRobot;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['grouprobot'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/grouprobot.php' => config_path('grouprobot.php'),
        ], 'grouprobot.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ymlluo'),
        ], 'grouprobot.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/ymlluo'),
        ], 'grouprobot.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ymlluo'),
        ], 'grouprobot.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
