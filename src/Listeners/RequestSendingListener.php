<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class RequestSendingListener
{
    public function handle(RequestSending $event){
        $tracing = (new OpenTelemetry)->startSpan($event->request->method() . " " . $event->request->url(), [
            "laravel.http.url" => $event->request->url(),
            "laravel.http.data" => $event->request->data(),
            "laravel.http.headers" => $event->request->headers()
        ]);
        $tracing->endSpan();
    }
}
