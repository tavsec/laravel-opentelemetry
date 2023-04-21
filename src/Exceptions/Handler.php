<?php

namespace Tavsec\LaravelOpentelemetry\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;
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

            $tracing = (new OpenTelemetry)->startSpan("exception", [
                "laravel.exception.message" => $e->getMessage(),
                "laravel.exception.trace" => json_encode($e->getTrace()),
                "laravel.exception.file" => $e->getFile(),
                "laravel.exception.line" => $e->getLine(),
            ]);
            $tracing->setSpanStatusCode(StatusCode::STATUS_ERROR);
            $tracing->recordException($e);
            $tracing->endSpan();
        });
    }
}
