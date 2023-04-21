<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class QueryExecutedListener
{
    public function handle(QueryExecuted $event){

        $tracing = (new OpenTelemetry)->startSpan("query-executed", [
            "laravel.sql.query" => $event->sql,
            "laravel.sql.connection" => $event->connectionName,
            "laravel.sql.bindings" => json_encode($event->bindings),
        ]);
        $tracing->endSpan();

    }
}
