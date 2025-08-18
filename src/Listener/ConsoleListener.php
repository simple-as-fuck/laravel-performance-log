<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;

/**
 * @deprecated use composer package: simple-as-fuck/php-performance-log
 */
class ConsoleListener
{
    public function __construct(
        private readonly \SimpleAsFuck\PerformanceLog\Listener\ConsoleListener $consoleListener,
    ) {
    }

    public function onCommandStart(CommandStarting $commandStarting): void
    {
        $this->consoleListener->onCommandStart($commandStarting->command);
    }

    public function onCommandFinish(CommandFinished $commandFinished): void
    {
        $this->consoleListener->onCommandFinish($commandFinished->command);
    }
}
