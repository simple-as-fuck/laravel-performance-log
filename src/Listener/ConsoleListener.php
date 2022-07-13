<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;
use SimpleAsFuck\Validator\Factory\Validator;

class ConsoleListener
{
    private PerformanceLogConfig $performanceLogConfig;
    private LogManager $logManager;
    private Repository $config;
    private Stopwatch $stopwatch;

    private Measurement $measurement;

    public function __construct(
        PerformanceLogConfig $performanceLogConfig,
        LogManager $logManager,
        Repository $config,
        Stopwatch $stopwatch
    ) {
        $this->performanceLogConfig = $performanceLogConfig;
        $this->logManager = $logManager;
        $this->config = $config;
        $this->stopwatch = $stopwatch;

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

        $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());

        if ($threshold === 0.0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $time = $this->stopwatch->checkPrefix($this->measurement, $threshold * 1000, $commandFinished->command);
            $logger->debug('Console command time: '.($time / 1000).'s name: "'.$commandFinished->command.'" pid: '.\getmypid());
            return;
        }

        $this->stopwatch->checkPrefix($this->measurement, $threshold * 1000, $commandFinished->command, static fn (float $time) => $logger->warning('Console command is too slow time: '.($time / 1000).'s name: "'.$commandFinished->command.'" threshold: '.$threshold.'s pid: '.\getmypid()));
    }
}
