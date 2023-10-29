<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Log\LogManager;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;

class QueueListener
{
    private Measurement $measurement;

    public function __construct(
        private PerformanceLogConfig $performanceLogConfig,
        private Stopwatch $stopwatch,
        private LogManager $logManager,
    ) {
        $this->measurement = new Measurement();
    }

    public function onJobStart(JobProcessing $jobProcessing): void
    {
        $this->performanceLogConfig->restoreSlowJobThreshold();
        $this->stopwatch->start($this->measurement, $jobProcessing->job->getJobId());
    }

    public function onJobFinish(JobProcessed $jobProcessed): void
    {
        $threshold = $this->performanceLogConfig->getSlowJobThreshold();
        $this->performanceLogConfig->restoreSlowJobThreshold();
        if ($threshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->performanceLogConfig->getLogChannelName());

        if ($threshold === 0.0 && $this->performanceLogConfig->isDebugEnabled()) {
            $time = $this->stopwatch->checkPrefix($this->measurement, $threshold, $jobProcessed->job->getJobId());
            $logger->debug('Queue job time: ' . $time . 'ms class: "' . $jobProcessed->job::class . '" pid: ' . \getmypid());
            return;
        }

        $this->stopwatch->checkPrefix(
            $this->measurement,
            $threshold,
            $jobProcessed->job->getJobId(),
            static fn (float $time) => $logger->warning('Queue job is too slow: ' . $time . 'ms class: "' . $jobProcessed->job::class . '" threshold: ' . $threshold . ' pid: ' . \getmypid())
        );
    }
}
