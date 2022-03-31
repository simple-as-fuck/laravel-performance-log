# Simple as fuck / Laravel performance log

Laravel service for logging slow parts of application. 

## Installation

```console
composer require simple-as-fuck/laravel-performance-log
```

## Configuration

```console
php artisan vendor:publish --tag performance-log-config
```

## Support

If any PHP platform requirements in [composer.json](../composer.json) ends with security support,
consider package version as unsupported except last version.

[PHP supported versions](https://www.php.net/supported-versions.php).

## Http middleware usage

For http request time logging you must register `PerformanceMiddleware`.
Look at [laravel documentation](https://laravel.com/docs/middleware) how to use laravel middlewares.

Recommended usage is register middleware as [global](https://laravel.com/docs/middleware#global-middleware) on **first position** and all of your request will be measured. 

If you want register middleware on route group you must configure 
[middleware priority](https://laravel.com/docs/middleware#sorting-middleware)
and put `PerformanceMiddleware` on **first position**.

## Thresholds overwrite

### Sql

If you know than some sql is slow, and you are fine with that you can overwrite `'performance_log.database.slow_query_threshold'`
by setting temporary threshold in `PerformanceLogConfig`.

```php
/** @var \SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig $config */
$config = app()->make(\SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig::class);

$threshold = $config->setSlowSqlQueryThreshold(null);

// run some slow queries without annoying performance log

$threshold->restore();
```

### Http

With group usage you can turn on measuring only on some routes or
configure different thresholds on different route by [middleware parameter](https://laravel.com/docs/middleware#middleware-parameters)
`threshold` float value in milliseconds, middleware parameter overwrite `'performance_log.http.slow_request_threshold'` config value.

## Usage with monitoring

Is recommended send performance warning logs into your monitoring system, so you know what is slow.

For simple monitoring is [laravel sentry](https://docs.sentry.io/platforms/php/guides/laravel/) integration.
Sentry integration can collect information about request or command with stacktrace,
this can make finding slow query much easier.
