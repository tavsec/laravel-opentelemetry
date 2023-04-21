<?php

namespace Tavsec\LaravelOpentelemetry\Facades;

use Illuminate\Support\Facades\Facade;

class OpenTelemetry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'opentelemetry';
    }
}
