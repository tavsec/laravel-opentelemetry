<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Cache\Events\CacheHit;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class CacheHitListener
{
    public function handle(CacheHit $event){
        $tracing = (new OpenTelemetry)->startSpan("cache-hit", [
            "laravel.cache-hit.key" => $event->key,
            "laravel.cache-hit.value" => json_encode($event->value),
        ]);
        $tracing->endSpan();
    }
}
