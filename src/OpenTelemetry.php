<?php

namespace Tavsec\LaravelOpentelemetry;

use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;

class OpenTelemetry
{
    private SpanInterface $span;
    private ScopeInterface $scope;
    public function getTracer(): TracerInterface|null{
        return Config::get("tracer");
    }

    public function info(){
        return $this;
    }

    public function startSpan(string $spanName, array $attributes = []){
        $tracer = $this->getTracer();
        if ($tracer) {
            $this->span = $tracer->spanBuilder($spanName)->setSpanKind(SpanKind::KIND_CONSUMER)->startSpan();
            $this->span->setAttributes($attributes);
            $this->scope = $this->span->activate();
        }

        return $this;
    }

    public function setSpanStatusCode(string $statusCode = StatusCode::STATUS_OK){
        $this->span->setStatus($statusCode, true);
        return $this;
    }

    public function recordException(\Throwable $exception){
        $this->span->recordException($exception, $exception->getTrace());
        return $this;
    }

    public function endSpan(){
        $this->span->end();
        $this->scope->detach();
        return $this;
    }
}
