<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Service;

use Illuminate\Contracts\Config\Repository;
use SimpleAsFuck\LaravelPerformanceLog\Model\TemporaryThreshold;
use SimpleAsFuck\Validator\Factory\Validator;

class PerformanceLogConfig
{
    private Repository $config;
    /** @var \WeakReference<TemporaryThreshold>|null  */
    private ?\WeakReference $temporarySqlQueryThreshold;
    private ?TemporaryThreshold $temporaryRequestThreshold;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->temporarySqlQueryThreshold = null;
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getSlowSqlQueryThreshold(): ?float
    {
        $temporaryThreshold = self::getTemporaryThreshold($this->temporarySqlQueryThreshold);
        if ($temporaryThreshold !== null) {
            return $temporaryThreshold->getValue();
        }

        return Validator::make($this->config->get('performance_log.database.slow_query_threshold'))->float()->min(0)->nullable();
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getSlowRequestThreshold(): ?float
    {
        if ($this->temporaryRequestThreshold !== null) {
            return $this->temporaryRequestThreshold->getValue();
        }

        return Validator::make($this->config->get('performance_log.http.slow_request_threshold'))->float()->min(0)->nullable();
    }

    public function isSlowRequestThresholdTemporary(): bool
    {
        return $this->temporaryRequestThreshold !== null;
    }

    /**
     * @param float|null $threshold threshold value in milliseconds
     */
    public function setSlowSqlQueryThreshold(?float $threshold): TemporaryThreshold
    {
        $temporaryThreshold = self::getTemporaryThreshold($this->temporarySqlQueryThreshold);
        if ($temporaryThreshold !== null) {
            return new TemporaryThreshold($threshold, $temporaryThreshold->getValue());
        }

        $temporaryThreshold = new TemporaryThreshold($threshold, $this->getSlowSqlQueryThreshold());
        $this->temporarySqlQueryThreshold = \WeakReference::create($temporaryThreshold);

        return $temporaryThreshold;
    }

    /**
     * @param float|null $threshold value in milliseconds
     */
    public function setSlowRequestThreshold(?float $threshold): void
    {
        if ($this->temporaryRequestThreshold === null) {
            $this->temporaryRequestThreshold = new TemporaryThreshold($threshold, null);
        }
    }

    public function restoreSlowRequestThreshold(): void
    {
        $this->temporaryRequestThreshold = null;
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
