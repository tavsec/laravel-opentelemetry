<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\TracerProvider;

class RequestSendingListener
{
    public function handle(RequestSending $event){
        $tracer = Config::get("tracer");
        if ($tracer) {
            /** @var Span $span */

            $span = $tracer->spanBuilder("external-request-start")->setSpanKind(SpanKind::KIND_CONSUMER)->startSpan();
            $span->setAttributes([
                "laravel.http.url" => $event->request->url(),
                "laravel.http.data" => $event->request->data(),
                "laravel.http.headers" => $event->request->headers()
            ]);
            $spanScope = $span->activate();
            $span->end();
            $spanScope->detach();

        }

    }
}
