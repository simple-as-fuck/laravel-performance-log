<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Listener;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

/**
 * @deprecated use composer package: simple-as-fuck/php-performance-log
 */
class QueueListener
{
    public function __construct(
        private readonly \SimpleAsFuck\PerformanceLog\Listener\QueueListener $queueListener,
    ) {
    }

    public function onJobStart(JobProcessing $jobProcessing): void
    {
        $this->queueListener->onJobStart($jobProcessing->job->getJobId());
    }

    public function onJobFinish(JobProcessed $jobProcessed): void
    {
        $this->queueListener->onJobFinish($jobProcessed->job->resolveName(), $jobProcessed->job->getJobId());
    }
}
