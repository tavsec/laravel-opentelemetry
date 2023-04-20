<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Route;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

class RequestHandledListener
{
    public function handle(RequestHandled $event)
    {
        $tracer = (new TracerProvider(
            [
                new SimpleSpanProcessor(
                    new ZipkinExporter(
                        config("app.name") . " (" . config("app.env") . ")",
                        PsrTransportFactory::discover()->create(config("opentelemetry.zipkin_url") . '/api/v2/spans', 'application/json')
                    ),
                ),
            ],
            new AlwaysOnSampler(),
        ))->getTracer(Route::current()?->getName() ?? $event->request->url());

        $span = $tracer->spanBuilder("routing")->setSpanKind(SpanKind::KIND_SERVER)->startSpan();
        $span->setAttribute("environment", config("app.env"));
        $span->setAttribute("body", json_encode($event->request->all()));
        $span->setAttribute("status", $event->response->status());
        $span->setStatus($event->response->status());

        if(!$event->response->isOk()){
            $span->recordException($event->response->exception);
        }

        $spanScope = $span->activate();

        $span->end();
        $spanScope->detach();
    }
}
