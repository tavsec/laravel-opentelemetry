<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Str;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class ResponseReceivedListener
{
    public function handle(ResponseReceived $event){
        $end = now();
        $start = $end->copy()->sub((float) $event->response->transferStats->getTransferTime() * 1000, "milliseconds");
        $tracing = (new OpenTelemetry)->startSpan($event->request->method() . " " . $event->request->url(), [
            "laravel.http.request.url" => $event->request->url(),
            "laravel.http.request.headers" => $event->request->headers(),
            "laravel.http.request.body" => $event->request->data(),
            "laravel.http.response.headers" => $event->response->headers(),
            "laravel.http.response.body" => $event->response->body(),
            "laravel.http.response.status" => $event->response->status(),
        ], (int) $start->getPreciseTimestamp() * 1_000);
        $tracing->endSpan((int) $end->getPreciseTimestamp() * 1_000);
    }
}
