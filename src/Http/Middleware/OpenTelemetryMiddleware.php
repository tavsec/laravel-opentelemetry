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
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessorBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class OpenTelemetryMiddleware
{
    public function handle(\Illuminate\Http\Request $request, Closure $next)
    {
        if(!config("opentelemetry.enabled"))
            return $next($request);
        $resource = ResourceInfoFactory::merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => config("app.name"),
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => config("app.env"),
        ])), ResourceInfoFactory::defaultResource());

        $spanProcessor = null;
        if(config("opentelemetry.url") === SimpleSpanProcessor::class){
            $spanProcessor =
                new SimpleSpanProcessor(
                    new ZipkinExporter(
                        config("app.name") . " (" . config("app.env") . ")",
                        PsrTransportFactory::discover()->create(config("opentelemetry.url"), 'application/json')
                    ),
                );
        }else if(config("opentelemetry.url") === BatchSpanProcessor::class){
            $spanProcessor = (new BatchSpanProcessorBuilder(
                new ZipkinExporter(
                    config("app.name") . " (" . config("app.env") . ")",
                    PsrTransportFactory::discover()->create(config("opentelemetry.url"), 'application/json')
                ),
            ))->build();
        }

        $tracerProvider = (new TracerProvider(
            [
                $spanProcessor
            ],
            new AlwaysOnSampler(),
            $resource,
            null,
            new RandomIdGenerator()
        ));
        $tracer = $tracerProvider->getTracer("tracer");

        Config::set("tracer", $tracer);

        $auth = Auth::user();
        $tracing = (new OpenTelemetry)->startSpan($request->method() . " " .$request->path(), [
            "environment" => config("app.env"),
            "body" => json_encode($this->maskBodyParameters($request->all())),
            "http.headers" => json_encode($this->maskHeaderParameters($request->headers->all())),
            "http.method" => $request->method(),
            "http.route" => $request->path(),
            "user.id" => $auth ? $auth->getAuthIdentifier() : null
        ]);

        $response = $next($request);
        $tracing->setAttribute("http.status_code", $response->status());

        $tracing->setSpanStatusCode($response->isOk() ? StatusCode::STATUS_OK : StatusCode::STATUS_ERROR);
        if(!$response->isOk()){
            if ($response->exception)
                $tracing->recordException($response->exception);
        }
        $tracing->endSpan();
        if(\config("opentelemetry.flush_batch_on_request"))
            $tracerProvider->forceFlush();
        return $response;
    }

    private function maskBodyParameters(array $body){
        return collect($body)->mapWithKeys(function ($el, $key){
            return [$key => OpenTelemetry::maskValue($key, $el)];
        });
    }
    private function maskHeaderParameters(array $headers){
        return collect($headers)->map(function ($el, $key){
            return collect($el)->map(function ($el) use ($key){
                return OpenTelemetry::maskValue($key, $el);
            });
        });
    }
}
