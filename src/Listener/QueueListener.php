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

        /** @var string|int $jobId */
        $jobId = $jobProcessing->job->getJobId();
        $this->stopwatch->start($this->measurement, (string) $jobId);
    }

    public function onJobFinish(JobProcessed $jobProcessed): void
    {
        $threshold = $this->performanceLogConfig->getSlowJobThreshold();
        $this->performanceLogConfig->restoreSlowJobThreshold();
        if ($threshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->performanceLogConfig->getLogChannelName());
        /** @var string|int $jobId */
        $jobId = $jobProcessed->job->getJobId();

        if ($threshold === 0.0 && $this->performanceLogConfig->isDebugEnabled()) {
            $time = $this->stopwatch->checkPrefix($this->measurement, $threshold, (string) $jobId);
            $logger->debug('Queue job time: ' . $time . 'ms job name: "' . $jobProcessed->job->resolveName() . '" pid: ' . \getmypid());
            return;
        }

        $this->stopwatch->checkPrefix(
            $this->measurement,
            $threshold,
            (string) $jobId,
            static fn (float $time) => $logger->warning('Queue job is too slow: ' . $time . 'ms job name: "' . $jobProcessed->job->resolveName() . '" threshold: ' . $threshold . 'ms pid: ' . \getmypid())
        );
    }
}
