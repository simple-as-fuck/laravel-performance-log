<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Provider;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use SimpleAsFuck\LaravelPerformanceLog\Listener\ConsoleListener;
use SimpleAsFuck\LaravelPerformanceLog\Listener\DatabaseListener;
use SimpleAsFuck\LaravelPerformanceLog\Listener\QueueListener;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;

class PackageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PerformanceLogConfig::class);
        $this->app->singleton(DatabaseListener::class);
        $this->app->singleton(ConsoleListener::class);
        $this->app->singleton(QueueListener::class);
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
        $databaseDispatcher = $databaseManager->connection()->getEventDispatcher();
        $databaseListener = $this->app->make(DatabaseListener::class);

        $databaseDispatcher->listen(QueryExecuted::class, [$databaseListener, 'onSqlQuery']);
        $databaseDispatcher->listen(TransactionBeginning::class, [$databaseListener, 'onTransactionBegin']);
        $databaseDispatcher->listen(TransactionCommitted::class, [$databaseListener, 'onTransactionCommit']);

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);
        $consoleListener = $this->app->make(ConsoleListener::class);

        $dispatcher->listen(CommandStarting::class, [$consoleListener, 'onCommandStart']);
        $dispatcher->listen(CommandFinished::class, [$consoleListener, 'onCommandFinish']);

        $queueListener = $this->app->make(QueueListener::class);
        $dispatcher->listen(JobProcessing::class, [$queueListener, 'onJobStart']);
        $dispatcher->listen(JobProcessed::class, [$queueListener, 'onJobFinish']);
    }
}
