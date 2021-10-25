<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;

class DatabaseListener
{
    private Repository $config;
    private LogManager $logManager;
    private Stopwatch $stopwatch;
    private PerformanceLogConfig $performanceLogConfig;

    private Measurement $transactionMeasurement;

    public function __construct(Repository $config, LogManager $logManager, Stopwatch $stopwatch, PerformanceLogConfig $performanceLogConfig)
    {
        $this->config = $config;
        $this->logManager = $logManager;
        $this->stopwatch = $stopwatch;
        $this->performanceLogConfig = $performanceLogConfig;

        $this->transactionMeasurement = new Measurement();
    }

    public function onSqlQuery(QueryExecuted $query): void
    {
        $queryThreshold = $this->performanceLogConfig->getSlowSqlQueryThreshold();
        if ($queryThreshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->config->get('performance_log.log_channel'));

        if ($queryThreshold === 0.0 && $this->config->get('app.debug')) {
            $logger->debug('Database query time: '.$query->time.'ms sql: "'.$query->sql.'" connection: "'.$query->connectionName.'"');
            return;
        }

        if ($query->time >= $queryThreshold) {
            $logger->warning('Database query is too slow: '.$query->time.'ms sql: "'.$query->sql.'" threshold: '.$queryThreshold. 'ms connection: "'.$query->connectionName.'"');
        }
    }

    public function onTransactionBegin(TransactionBeginning $transactionBeginning): void
    {
        if ($transactionBeginning->connection->transactionLevel() !== 1) {
            return;
        }

        $transactionThreshold = $this->config->get('performance_log.database.slow_transaction_threshold');
        if ($transactionThreshold === null) {
            return;
        }

        if ($transactionThreshold == 0 && $this->config->get('app.debug')) {
            $logger = $this->logManager->channel($this->config->get('performance_log.log_channel'));
            $logger->debug('Database transaction begin connection: "'.$transactionBeginning->connectionName.'"');
        }

        $this->stopwatch->start($this->transactionMeasurement, $transactionBeginning->connectionName);
    }

    public function onTransactionCommit(TransactionCommitted $transactionCommitted): void
    {
        if ($transactionCommitted->connection->transactionLevel() !== 0) {
            return;
        }

        $transactionThreshold = $this->config->get('performance_log.database.slow_transaction_threshold');
        if ($transactionThreshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->config->get('performance_log.log_channel'));

        if (! $this->transactionMeasurement->running($transactionCommitted->connectionName)) {
            $logger->error('Database transaction measurement not running database connection: "'.$transactionCommitted->connectionName.'", check if begin transaction is called before commit!');
            return;
        }

        if ($transactionThreshold == 0 && $this->config->get('app.debug')) {
            $time = $this->stopwatch->checkPrefix($this->transactionMeasurement, $transactionThreshold, $transactionCommitted->connectionName);
            $logger->debug('Database transaction commit time: '.$time.'ms connection: "'.$transactionCommitted->connectionName.'"');
            return;
        }

        $this->stopwatch->checkPrefix(
            $this->transactionMeasurement,
            $transactionThreshold,
            $transactionCommitted->connectionName,
            fn (float $time) => $logger->warning('Database transaction is too slow: '.$time.'ms threshold: '.$transactionThreshold. 'ms connection: "'.$transactionCommitted->connectionName.'"')
        );
    }
}
