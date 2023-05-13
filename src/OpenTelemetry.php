<?php

namespace Tavsec\LaravelOpentelemetry;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use Throwable;

/**
 *
 */
class OpenTelemetry
{
    /**
     * @var array
     */
    private array $span;
    /**
     * @var array
     */
    private array $scope;

    /**
     *
     */
    public function __construct()
    {
        $this->span = [];
        $this->scope = [];
    }

    /**
     * @return TracerInterface|null
     */
    public function getTracer(): ?TracerInterface
    {
        return Config::get("tracer");
    }

    /**
     * @param string $spanName
     * @param array $attributes
     * @param int|null $start
     * @param $spanKind
     * @return $this
     */
    public function startSpan(string $spanName, array $attributes = [], ?int $start = null, $spanKind = SpanKind::KIND_CONSUMER)
    {
        $tracer = $this->getTracer();
        if ($tracer) {
            $builder = $tracer->spanBuilder($spanName)->setSpanKind($spanKind);

            if ($start) {
                $builder = $builder->setStartTimestamp($start);
            }
            $span = $builder->startSpan();

            $span->setAttributes($this->processAttributes($attributes));
            $this->scope[] = $span->activate();
            $this->span[] = $span;
        }

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $lastSpan = $this->getLastSpan();
        if ($lastSpan) $lastSpan->setAttribute($key, Str::limit($value, config("opentelemetry.attribute_length_limit"), ""));
        return $this;
    }

    /**
     * @param string $statusCode
     * @return $this
     */
    public function setSpanStatusCode(string $statusCode = StatusCode::STATUS_OK)
    {
        $lastSpan = $this->getLastSpan();
        if ($lastSpan) $lastSpan->setStatus($statusCode, true);
        return $this;
    }

    /**
     * @param Throwable $exception
     * @return $this
     */
    public function recordException(Throwable $exception)
    {
        $lastSpan = $this->getLastSpan();
        if ($lastSpan) $lastSpan->recordException($exception, $exception->getTrace());
        return $this;
    }

    /**
     * @param $end
     * @return $this
     */
    public function endSpan($end = null)
    {
        $span = array_pop($this->span);
        $scope = array_pop($this->scope);
        if ($span) {
            $span->end($end);
            $scope->detach();
        }
        return $this;
    }

    /**
     * @return SpanInterface|null
     */
    private function getLastSpan(): ?SpanInterface
    {
        return count($this->span) > 1 ? $this->span[count($this->span) - 1] : null;
    }

    /**
     * @param array $attributes
     * @return mixed[]
     */
    private function processAttributes(array $attributes)
    {
        return collect($attributes)->mapWithKeys(fn($el, $key) => [$key =>  Str::limit($el, config("opentelemetry.attribute_length_limit"), "")])->toArray();
    }

    public static function maskValue($key, $value){

        if(!in_array($key, array_map('strtolower', config("opentelemetry.masked_attributes"))))
            return $value;
        return Str::mask($value, "*", 0, -config("opentelemetry.masked_attributes_shown_characters"), );
    }

}
