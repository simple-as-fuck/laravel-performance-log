<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Provider;

use Illuminate\Support\ServiceProvider;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;

/**
 * @deprecated use SimpleAsFuck\PerformanceLog\Provider\LaravelProvider from composer package: simple-as-fuck/php-performance-log
 */
class PackageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PerformanceLogConfig::class);
    }

    public function boot(): void
    {
    }
}
