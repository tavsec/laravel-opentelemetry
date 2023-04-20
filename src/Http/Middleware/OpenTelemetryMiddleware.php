<?php

namespace Tavsec\LaravelOpentelemetry\Http\Middleware;

use Closure;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;


class OpenTelemetryMiddleware
{
    public function handle($request, Closure $next)
    {

        $tracer = (new TracerProvider(
            [
                new SimpleSpanProcessor(
                    new ZipkinExporter(
                        config("app.name"),
                        PsrTransportFactory::discover()->create(config("opentelemetry.zipkin_url") . '/api/v2/spans', 'application/json')
                    ),
                ),
            ],
            new AlwaysOnSampler(),
        ))->getTracer('Hello World Laravel Web Server');

        $span = $tracer->spanBuilder($request->url())->startSpan();
        $spanScope = $span->activate();

        $r = $next($request);

        $span->end();
        $spanScope->detach();

        return $r;
    }
}
