<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Console\Events\ScheduledTaskFinished;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class ScheduledTaskFinishedListener
{
    public function handle(ScheduledTaskFinished $event){
        $end = now();
        $start = $end->copy()->sub((float) $event->runtime, 'milliseconds');

        $tracing = (new OpenTelemetry)->startSpan("task-finished", [
            "laravel.task.command" => $event->task->command,
            "laravel.task.runtime" => $event->runtime,
        ], (int) $start->getPreciseTimestamp() * 1_000);
        $tracing->endSpan((int) $end->getPreciseTimestamp() * 1_000);
    }
}
