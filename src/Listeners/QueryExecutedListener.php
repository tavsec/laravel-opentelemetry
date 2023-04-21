<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;

class QueryExecutedListener
{
    public function handle(QueryExecuted $event){
        /** @var TracerInterface|null $tracer */
        $tracer = Config::get("tracer");
        if ($tracer) {

            $span = $tracer->spanBuilder("query-executed")->startSpan();
            $spanScope = $span->activate();
            $span->setAttribute("laravel.sql.query", $event->sql);
            $span->setAttribute("laravel.sql.connection", $event->connectionName);
            $span->setAttribute("laravel.sql.bindings", json_encode($event->bindings));
            $span->end();
            $spanScope->detach();
        }
    }
}
