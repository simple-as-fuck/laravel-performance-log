<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Provider;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Support\ServiceProvider;
use SimpleAsFuck\LaravelPerformanceLog\Listener\DatabaseListener;

class PackageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DatabaseListener::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/performance_log.php', 'performance_log');
        $this->publishes([
            __DIR__.'/../../config/performance_log.php' => $this->app->configPath('performance_log.php'),
        ], 'performance-log-config');

        $this->app->make('events');

        /** @var DatabaseManager $databaseManager */
        $databaseManager = $this->app->make(DatabaseManager::class);
        $databaseDispatcher = $databaseManager->getEventDispatcher();
        $databaseListener = $this->app->make(DatabaseListener::class);

        $databaseDispatcher->listen(QueryExecuted::class, [$databaseListener, 'onSqlQuery']);
        $databaseDispatcher->listen(TransactionBeginning::class, [$databaseListener, 'onTransactionBegin']);
        $databaseDispatcher->listen(TransactionCommitted::class, [$databaseListener, 'onTransactionCommit']);
    }
}
