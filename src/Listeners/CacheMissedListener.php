<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class CacheMissedListener
{
    public function handle(CacheMissed $event){
        $tracing = (new OpenTelemetry)->startSpan("cache-missed " . $event->key, [
            "laravel.cache-missed.key" => $event->key,
        ]);
        $tracing->endSpan();
    }
}
