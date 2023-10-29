<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;

class ConsoleListener
{
    private Measurement $measurement;

    public function __construct(
        private PerformanceLogConfig $performanceLogConfig,
        private LogManager $logManager,
        private Stopwatch $stopwatch
    ) {
        $this->measurement = new Measurement();
    }

    public function onCommandStart(CommandStarting $commandStarting): void
    {
        $this->performanceLogConfig->restoreSlowCommandThreshold();
        $this->measurement->start($commandStarting->command);
    }

    public function onCommandFinish(CommandFinished $commandFinished): void
    {
        $threshold = $this->performanceLogConfig->getSlowCommandThreshold();

        $this->performanceLogConfig->restoreSlowCommandThreshold();
        if ($threshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->performanceLogConfig->getLogChannelName());

        if ($threshold === 0.0 && $this->performanceLogConfig->isDebugEnabled()) {
            $time = $this->stopwatch->checkPrefix($this->measurement, $threshold * 1000, $commandFinished->command);
            $logger->debug('Console command time: '.($time / 1000).'s name: "'.$commandFinished->command.'" pid: '.\getmypid());
            return;
        }

        $this->stopwatch->checkPrefix($this->measurement, $threshold * 1000, $commandFinished->command, static fn (float $time) => $logger->warning('Console command is too slow time: '.($time / 1000).'s name: "'.$commandFinished->command.'" threshold: '.$threshold.'s pid: '.\getmypid()));
    }
}
