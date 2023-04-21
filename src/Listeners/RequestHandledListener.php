<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Config;
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


    }
}
