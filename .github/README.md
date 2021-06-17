# Simple as fuck / Laravel performance log

Laravel service for logging slow parts of application. 

## Installation

```console
composer require simple-as-fuck/laravel-performance-log
```

## Configuration

Add this into your laravel application: `config/app.php`. 

```php
    'performance' => [
        // configure name of log chanel defined in config/logging.php used for logging
        // with null value default log chanel will be used 
        'log_channel' => null,
        // thresholds definitions, if anything run longer than threshold service will log warning
        // all thresholds are float value in milliseconds
        // null threshold will turn off any measurring
        // threshold zero value with 'app.debug' true value will log running time for anything as debug
        'database' => [
            'slow_query_threshold' => 50,
            'slow_transaction_threshold' => 300,
        ],
        'http' => [
            'slow_request_threshold' => 1000,
        ],
    ],
```

## Http middleware usage

For http request time logging you must register `PerformanceMiddleware`.
Look at [laravel documentation](https://laravel.com/docs/middleware) how to use laravel middlewares.

Recommended usage is register middleware as [global](https://laravel.com/docs/middleware#global-middleware) on **first position** and all of your request will be measured. 

If you want register middleware on route group you must configure 
[middleware priority](https://laravel.com/docs/middleware#sorting-middleware)
and put `PerformanceMiddleware` on **first position**.

With group usage you can turn on measuring only on some routes or
configure different thresholds on different route groups by [middleware parameter](https://laravel.com/docs/middleware#middleware-parameters)
`threshold` float value in milliseconds, middleware parameter overwrite `'app.performance.http.slow_request_threshold'` config value.

## Usage with monitoring

Is recommended send performance warning logs into you monitoring system, so you know what is slow.

For simple monitoring is [laravel sentry](https://docs.sentry.io/platforms/php/guides/laravel/) integration.
Sentry integration can collect information about request or command with stacktrace,
this can make finding slow query much easier.
