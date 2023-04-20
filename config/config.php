<?php
return [
    "exporter" => env("OPENTELEMETRY_EXPORTER", "zipkin"),
    "zipkin_url" => env("OPENTELEMETRY_ZIPKIN_URL", "http://zipkin:9411"),
    "jaeger_url" => env("OPENTELEMETRY_JAEGER_URL", "http://jaeger:16886")
];
