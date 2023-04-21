<?php

namespace Tavsec\LaravelOpentelemetry\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

class OpenTelemetryMiddleware
{
    public function handle($request, Closure $next)
    {
        $resource = ResourceInfoFactory::merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => config("app.name"),
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => config("app.env"),
        ])), ResourceInfoFactory::defaultResource());

        $tracer = (new TracerProvider(
            [
                new SimpleSpanProcessor(
                    new ZipkinExporter(
                        config("app.name") . " (" . config("app.env") . ")",
                        PsrTransportFactory::discover()->create(config("opentelemetry.url"), 'application/json')
                    ),
                ),
            ],
            new AlwaysOnSampler(),
            $resource,
            null,
            new RandomIdGenerator()
        ))->getTracer("tracer");


        Config::set("tracer", $tracer);

        $span = $tracer->spanBuilder("request")->startSpan();
        $spanScope = $span->activate();

        $response = $next($request);


        $span->setAttribute("service.name", "test-service");
        $span->setAttribute("environment", config("app.env"));
        $span->setAttribute("body", json_encode($request->all()));
        $span->setAttribute("status", $response->status());
        $span->setStatus($response->status());

        if(!$response->isOk()){
            $span->recordException($response->exception);
        }

        $span->end();
        $spanScope->detach();

        return $response;
    }
}
