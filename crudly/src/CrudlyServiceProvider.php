<?php

namespace Shahrakii\Crudly;

use Illuminate\Support\ServiceProvider;

class CrudlyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'crudly');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/crudly.php' => config_path('crudly.php'),
        ], 'crudly-config');
    }
}
