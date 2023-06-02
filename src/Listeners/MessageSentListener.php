<?php

namespace Tavsec\LaravelOpentelemetry\Listeners;

use Illuminate\Mail\Events\MessageSent;
use OpenTelemetry\API\Trace\SpanKind;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Tavsec\LaravelOpentelemetry\OpenTelemetry;

class MessageSentListener
{
    public function handle(MessageSent $event){
        $end = now();
        $start = $end->copy()->sub((float) $event->message->getDate(), 'milliseconds');
        $tracing = (new OpenTelemetry)->startSpan("message-sent " . $event->message->getSubject(), [
            "laravel.message.from" => json_encode(collect($event->message->getFrom())->map(fn(Address $el) => $el->getAddress())),
            "laravel.message.to" => json_encode(collect($event->message->getTo())->map(fn(Address $el) => $el->getAddress())),
            "laravel.message.bcc" => json_encode(collect($event->message->getBcc())->map(fn(Address $el) => $el->getAddress())),
            "laravel.message.cc" => json_encode(collect($event->message->getCc())->map(fn(Address $el) => $el->getAddress())),
            "laravel.message.message" => $event->message->getBody()->bodyToString(),
            "laravel.message.subject" => $event->message->getSubject(),
            "laravel.message.priority" => $event->message->getPriority(),
        ], (int) $start->getPreciseTimestamp() * 1_000, SpanKind::KIND_CLIENT);

        $tracing->endSpan((int) $end->getPreciseTimestamp() * 1_000);
    }
}
