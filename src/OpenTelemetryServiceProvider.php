<?php

namespace Tavsec\LaravelOpentelemetry;

use Illuminate\Support\ServiceProvider;

class OpenTelemetryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'opentelemetry');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('opentelemetry.php'),
            ], 'config');

        }
    }
}
