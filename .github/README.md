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
or `'performance_log.database.slow_transaction_threshold'` by setting a temporary threshold in `PerformanceLogConfig`.

```php
/** @var \SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig $config */
$config = app()->make(\SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig::class);

$sqlThreshold = $config->setSlowSqlQueryThreshold(null);
$transactionThreshold = $config->setSlowDbTransactionThreshold(null);

// run some slow queries without annoying performance log

$sqlThreshold->restore();
$transactionThreshold->restore();
```

### Http

If you know that some concrete controller action is slow or should be extra fast,
you can overwrite `'performance_log.http.slow_request_threshold'` by setting a temporary threshold.
The temporary threshold can be set only once per request and live until request ends.

```php
/** @var \SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig $config */
$config = app()->make(\SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig::class);

$config->setSlowRequestThreshold(null);

// run some extra slow logic without annoying performance log

// no need for threshold restoring, performance middleware will handle it
```

### Console

If you want to overwrite `'performance_log.console.slow_command_threshold'`, you can do it by setting a temporary threshold.
The temporary threshold can be set only once per command run and live until command ends.

```php
/** @var \SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig $config */
$config = app()->make(\SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig::class);

$config->setSlowCommandThreshold(60); // one minute

// no need for threshold restoring, performance listener will handle it
```

## Usage with monitoring

Is recommended send performance warning logs into your monitoring system, so you know what is slow.

For simple monitoring is [laravel sentry](https://docs.sentry.io/platforms/php/guides/laravel/) integration.
Sentry integration can collect information about request or command with stacktrace,
this can make finding slow query much easier.
