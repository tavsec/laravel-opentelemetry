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
        $end = now();
        $start = $end->copy()->sub((float) $event->time, 'milliseconds');
        $tracing = (new OpenTelemetry)->startSpan("query-executed", [
            "laravel.query.query" => $event->sql,
            "laravel.query.duration" => $event->time,
            "db.system" => $event->connection->getDriverName(),
            "laravel.query.connection" => $event->connectionName,
            "laravel.query.bindings" => json_encode($event->bindings),
        ], (int) $start->getPreciseTimestamp() * 1_000, SpanKind::KIND_CLIENT);

        $tracing->endSpan((int) $end->getPreciseTimestamp() * 1_000);
    }
}
