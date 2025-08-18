<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;

/**
 * @deprecated use composer package: simple-as-fuck/php-performance-log
 */
class DatabaseListener
{
    public function __construct(
        private readonly \SimpleAsFuck\PerformanceLog\Listener\DatabaseListener $databaseListener,
    ) {
    }

    public function onSqlQuery(QueryExecuted $query): void
    {
        $this->databaseListener->onSqlQuery($query->sql, $query->time, $query->connectionName);
    }

    public function onTransactionBegin(TransactionBeginning $transactionBeginning): void
    {
        $this->databaseListener->onTransactionStart($transactionBeginning->connection->transactionLevel(), $transactionBeginning->connectionName);
    }

    public function onTransactionCommit(TransactionCommitted $transactionCommitted): void
    {
        $this->databaseListener->onTransactionFinnish($transactionCommitted->connection->transactionLevel(), $transactionCommitted->connectionName);
    }
}
