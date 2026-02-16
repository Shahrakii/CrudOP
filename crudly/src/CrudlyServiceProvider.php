<?php

namespace Shahrakii\Crudly;

use Illuminate\Support\ServiceProvider;
use Shahrakii\Crudly\Console\Commands\GenerateCrudCommand;
use Shahrakii\Crudly\Console\Commands\GenerateModelCommand;
use Shahrakii\Crudly\Console\Commands\GenerateControllerCommand;

class CrudlyServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/crudly.php',
            'crudly'
        );

        // Register singleton
        $this->app->singleton('crudly', function ($app) {
            return new Crudly($app);
        });
    }

    /**
     * Bootstrap application services
     */
    public function boot()
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateCrudCommand::class,
                GenerateModelCommand::class,
                GenerateControllerCommand::class,
            ]);
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'crudly');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/crudly.php' => config_path('crudly.php'),
        ], 'crudly-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/crudly'),
        ], 'crudly-views');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'crudly-migrations');
    }
}
