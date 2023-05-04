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
    private array $span;
    private array $scope;
    public function getTracer(): ?TracerInterface {
        return Config::get("tracer");
    }

    public function startSpan(string $spanName, array $attributes = [], ?int $start = null, $spanKind = SpanKind::KIND_CONSUMER){
        $tracer = $this->getTracer();
        if ($tracer) {
            $builder = $tracer
                ->spanBuilder($spanName)
                ->setSpanKind($spanKind);

            if ($start) {
                $builder = $builder->setStartTimestamp($start);
            }
            $span = $builder
                ->startSpan();

            $span->setAttributes($attributes);
            $this->scope[] = $span->activate();
            $this->span[] = $span;
        }

        return $this;
    }

    public function setAttribute($key, $value){
        $this->getLastSpan()->setAttribute($key, $value);
        return $this;
    }

    public function setSpanStatusCode(string $statusCode = StatusCode::STATUS_OK){
        $this->getLastSpan()->setStatus($statusCode, true);
        return $this;
    }

    public function recordException(\Throwable $exception){
        $this->getLastSpan()->recordException($exception, $exception->getTrace());
        return $this;
    }

    public function endSpan($end = null){
        $span = array_pop($this->span);
        $scope = array_pop($this->scope);
        if ($span) {
            $span->end($end);
            $scope->detach();
        }
        return $this;
    }

    private function getLastSpan(): SpanInterface
    {
        return $this->span[count($this->span) - 1];
    }
}
