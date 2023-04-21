<?php

namespace Tavsec\LaravelOpentelemetry;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Tavsec\LaravelOpentelemetry\Exceptions\Handler;
use Tavsec\LaravelOpentelemetry\Providers\EventServiceProvider;

class OpenTelemetryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'opentelemetry');
        $this->app->register(EventServiceProvider::class);
        $this->app->bind(ExceptionHandler::class, Handler::class);
        $this->app->bind('opentelemetry', function($app) {
            return new OpenTelemetry();
        });
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
