<?php

namespace Tavsec\LaravelOpentelemetry\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // \Illuminate\Database\Eloquent\ModelNotFoundException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            /** @var TracerInterface|null $tracer */
            $tracer = Config::get("tracer");
            if ($tracer) {

                $span = $tracer->spanBuilder("exception")->setSpanKind(SpanKind::KIND_SERVER)->startSpan();
                $spanScope = $span->activate();
                $span->setStatus(StatusCode::STATUS_ERROR, "true");
                $span->recordException($e, $e->getTrace());
                $span->end();
                $spanScope->detach();
            }
        });
    }
}
