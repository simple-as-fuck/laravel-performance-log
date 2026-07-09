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
        /** @phpstan-ignore-next-line cast.useless */
        $this->queueListener->onJobStart($jobProcessing->job->resolveName() . '-' . ((string) $jobProcessing->job->getJobId()));
    }

    public function onJobFinish(JobProcessed $jobProcessed): void
    {
        /** @phpstan-ignore-next-line cast.useless */
        $this->queueListener->onJobFinish($jobProcessed->job->resolveName(), $jobProcessed->job->resolveName() . '-' . ((string) $jobProcessed->job->getJobId()));
    }
}
