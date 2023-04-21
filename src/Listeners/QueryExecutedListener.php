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
            "laravel.query.query" => $event->sql,
            "laravel.query.duration" => $event->time,
            "db.system" => $event->connection->getDriverName(),
            "laravel.query.connection" => $event->connectionName,
            "laravel.query.bindings" => json_encode($event->bindings),
        ], SpanKind::KIND_CLIENT);
        $tracing->endSpan();

    }
}
