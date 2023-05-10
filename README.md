# [WIP] Laravel Open-Telemetry
This package provides OTEL exporter for Laravel applications.

## Usage
### Middleware
Middleware will track requests and export them to OTEL.
Add `OpenTelemetryMiddleware` to `app/Http/Kernel.php` file, at the end of `$middleware` array.

```php
protected $middleware = [
    // ...
    OpenTelemetryMiddleware::class
];
```
## Current reports 
- Cache hit/miss
- Request parameters
- Eloquent queries
