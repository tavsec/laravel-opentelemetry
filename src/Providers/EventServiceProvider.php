<?php

namespace Tavsec\LaravelOpentelemetry\Providers;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Http\Client\Events\RequestSending;
use Tavsec\LaravelOpentelemetry\Listeners\RequestHandledListener;
use Tavsec\LaravelOpentelemetry\Listeners\RequestSendingListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RequestHandled::class => [RequestHandledListener::class],
        RequestSending::class => [RequestSendingListener::class]
    ];

    public function boot()
    {
        parent::boot();
    }
}
