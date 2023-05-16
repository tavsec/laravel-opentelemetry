<?php
return [
    "url" => env("OPENTELEMETRY_URL", "http://localhost:9411"),
    "attribute_length_limit" => 4095,
    "masked_attributes" => ["password", "auth_token", "authorization", "laravel_session"],
    "masked_attributes_shown_characters" => 3,
    "span_processor" => \OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor::class,
    "enabled" => env("OPENTELEMETRY_ENABLED", true),
    "flush_batch_on_request" => env("OPENTELEMETRY_FLUSH_BATCH_ON_REQUEST", true)
];
