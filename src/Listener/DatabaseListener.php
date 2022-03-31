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
use SimpleAsFuck\Validator\Factory\Validator;

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

        $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());

        if ($queryThreshold === 0.0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $logger->debug('Database query time: '.$query->time.'ms sql: "'.$query->sql.'" connection: "'.$query->connectionName.'" pid: '.\getmypid());
            return;
        }

        if ($query->time >= $queryThreshold) {
            $logger->warning('Database query is too slow: '.$query->time.'ms sql: "'.$query->sql.'" threshold: '.$queryThreshold. 'ms connection: "'.$query->connectionName.'" pid: '.\getmypid());
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

        if ($transactionThreshold == 0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());
            $logger->debug('Database transaction begin connection: "'.$transactionBeginning->connectionName.'" pid: '.\getmypid());
        }

        $this->stopwatch->start($this->transactionMeasurement, $transactionBeginning->connectionName);
    }

    public function onTransactionCommit(TransactionCommitted $transactionCommitted): void
    {
        if ($transactionCommitted->connection->transactionLevel() !== 0) {
            return;
        }

        $transactionThreshold = Validator::make($this->config->get('performance_log.database.slow_transaction_threshold'))->float()->min(0)->nullable();
        if ($transactionThreshold === null) {
            return;
        }

        $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());

        if (! $this->transactionMeasurement->running($transactionCommitted->connectionName)) {
            $logger->error('Database transaction measurement not running database connection: "'.$transactionCommitted->connectionName.'" pid: '.\getmypid().', check if begin transaction is called before commit!');
            return;
        }

        if ($transactionThreshold == 0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $time = $this->stopwatch->checkPrefix($this->transactionMeasurement, $transactionThreshold, $transactionCommitted->connectionName);
            $logger->debug('Database transaction commit time: '.$time.'ms connection: "'.$transactionCommitted->connectionName.'" pid: '.\getmypid());
            return;
        }

        $this->stopwatch->checkPrefix(
            $this->transactionMeasurement,
            $transactionThreshold,
            $transactionCommitted->connectionName,
            fn (float $time) => $logger->warning('Database transaction is too slow: '.$time.'ms threshold: '.$transactionThreshold. 'ms connection: "'.$transactionCommitted->connectionName.'" pid: '.\getmypid())
        );
    }
}
