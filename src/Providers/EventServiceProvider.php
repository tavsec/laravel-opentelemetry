<?php

namespace Tavsec\LaravelOpentelemetry\Providers;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Tavsec\LaravelOpentelemetry\Listeners\RequestHandledListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RequestHandled::class => [RequestHandledListener::class]
    ];

    public function boot()
    {
        parent::boot();
    }
}
