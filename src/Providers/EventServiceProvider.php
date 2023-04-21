<?php

namespace Tavsec\LaravelOpentelemetry\Providers;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Tavsec\LaravelOpentelemetry\Listeners\CacheHitListener;
use Tavsec\LaravelOpentelemetry\Listeners\CacheMissedListener;
use Tavsec\LaravelOpentelemetry\Listeners\QueryExecutedListener;
use Tavsec\LaravelOpentelemetry\Listeners\RequestSendingListener;
use Tavsec\LaravelOpentelemetry\Listeners\ResponseReceivedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RequestSending::class => [RequestSendingListener::class],
        QueryExecuted::class => [QueryExecutedListener::class],
        CacheHit::class => [CacheHitListener::class],
        CacheMissed::class => [CacheMissedListener::class],
        ResponseReceived::class => [ResponseReceivedListener::class]
    ];

    public function boot()
    {
        parent::boot();
    }
}
