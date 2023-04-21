<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Http\Client\Events\ResponseReceived;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class ResponseReceivedListener
{
    public function handle(ResponseReceived $event){
        $tracing = (new OpenTelemetry)->startSpan($event->request->method() . " " . $event->request->url(), [
            "laravel.http.url" => $event->request->url(),
            "laravel.http.data" => $event->request->data(),
            "laravel.http.headers" => $event->request->headers(),
            "laravel.http.response.headers" => $event->response->headers(),
            "laravel.http.response.body" => $event->response->body()
        ]);
        $tracing->endSpan();
    }
}
