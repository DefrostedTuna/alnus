<?php

namespace DefrostedTuna\Alnus;

use Illuminate\Support\ServiceProvider;

class AlnusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/resources/migrations/');
        $this->publishes([
            __DIR__.'/resources/config/alnus.php' => config_path('alnus.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
