<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Service;

use Illuminate\Contracts\Config\Repository;
use SimpleAsFuck\LaravelPerformanceLog\Model\TemporaryThreshold;

class PerformanceLogConfig
{
    private Repository $config;
    /** @var \WeakReference<TemporaryThreshold>|null  */
    private ?\WeakReference $temporarySqlQueryThreshold;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->temporarySqlQueryThreshold = null;
    }

    public function getSlowSqlQueryThreshold(): ?float
    {
        $temporaryThreshold = self::getTemporaryThreshold($this->temporarySqlQueryThreshold);
        if ($temporaryThreshold) {
            return $temporaryThreshold->getValue();
        }

        return $this->config->get('performance_log.database.slow_query_threshold');
    }

    public function setSlowSqlQueryThreshold(?float $threshold): TemporaryThreshold
    {
        $temporaryThreshold = self::getTemporaryThreshold($this->temporarySqlQueryThreshold);
        if ($temporaryThreshold) {
            return new TemporaryThreshold($threshold, $temporaryThreshold->getValue());
        }

        $temporaryThreshold = new TemporaryThreshold($threshold, $this->getSlowSqlQueryThreshold());
        $this->temporarySqlQueryThreshold = \WeakReference::create($temporaryThreshold);

        return $temporaryThreshold;
    }

    /**
     * @param \WeakReference<TemporaryThreshold>|null $weakReference
     */
    private static function getTemporaryThreshold(?\WeakReference &$weakReference): ?TemporaryThreshold
    {
        if ($weakReference === null) {
            return null;
        }

        $threshold = $weakReference->get();
        if ($threshold === null || $threshold->isRestored()) {
            $weakReference = null;
            return null;
        }

        return $threshold;
    }
}
