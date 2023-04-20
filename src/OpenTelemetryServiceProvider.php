<?php

namespace Tavsec\LaravelOpentelemetry;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;
use Tavsec\LaravelOpentelemetry\Listeners\RequestHandledListener;
use Tavsec\LaravelOpentelemetry\Providers\EventServiceProvider;

class OpenTelemetryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'opentelemetry');
        $this->app->register(EventServiceProvider::class);
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
