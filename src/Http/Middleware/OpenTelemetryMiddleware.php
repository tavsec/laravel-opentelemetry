<?php

namespace Tavsec\LaravelOpentelemetry\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
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
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class OpenTelemetryMiddleware
{
    public function handle(\Illuminate\Http\Request $request, Closure $next)
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

        $auth = Auth::user();
        $tracing = (new OpenTelemetry)->startSpan($request->method() . " " .$request->path(), [
            "environment" => config("app.env"),
            "body" => json_encode($request->all()),
            "http.method" => $request->method(),
            "http.route" => $request->path(),
            "user.id" => $auth ? $auth->getAuthIdentifier() : null
        ]);

        $response = $next($request);
        $tracing->setAttribute("http.status_code", $response->status());

        $tracing->setSpanStatusCode($response->isOk() ? StatusCode::STATUS_OK : StatusCode::STATUS_ERROR);
        if(!$response->isOk()){
            $tracing->recordException($response->exception);
        }
        $tracing->endSpan();

        return $response;
    }
}
