<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Service;

use Illuminate\Contracts\Config\Repository;
use SimpleAsFuck\LaravelPerformanceLog\Model\TemporaryThreshold;
use SimpleAsFuck\Validator\Factory\Validator;
use SimpleAsFuck\Validator\Rule\General\Rules;

class PerformanceLogConfig
{
    private Repository $config;
    /** @var \WeakReference<TemporaryThreshold>|null  */
    private ?\WeakReference $temporarySqlQueryThreshold;
    /** @var \WeakReference<TemporaryThreshold>|null */
    private ?\WeakReference $temporaryDbTransactionThreshold;
    private ?TemporaryThreshold $temporaryRequestThreshold;
    private ?TemporaryThreshold $temporaryCommandThreshold;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->temporarySqlQueryThreshold = null;
        $this->temporaryDbTransactionThreshold = null;
        $this->temporaryRequestThreshold = null;
        $this->temporaryCommandThreshold = null;
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getSlowSqlQueryThreshold(): ?float
    {
        return self::getTemporaryThreshold($this->temporarySqlQueryThreshold)
            ?->getValue()
            ??
            $this->getConfigValue('performance_log.database.slow_query_threshold')->float()->min(0)->nullable()
        ;
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getSlowDbTransactionThreshold(): ?float
    {
        return self::getTemporaryThreshold($this->temporaryDbTransactionThreshold)
            ?->getValue()
            ??
            $this->getConfigValue('performance_log.database.slow_transaction_threshold')->float()->min(0)->nullable()
        ;
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getSlowRequestThreshold(): ?float
    {
        if ($this->temporaryRequestThreshold !== null) {
            return $this->temporaryRequestThreshold->getValue();
        }

        return $this->getConfigValue('performance_log.http.slow_request_threshold')->float()->min(0)->nullable();
    }

    /**
     * @return float|null threshold value in seconds
     */
    public function getSlowCommandThreshold(): ?float
    {
        if ($this->temporaryCommandThreshold !== null) {
            return $this->temporaryCommandThreshold->getValue();
        }

        return $this->getConfigValue('performance_log.console.slow_command_threshold')->float()->min(0)->nullable();
    }

    /**
     * @param float|null $threshold threshold value in seconds
     */
    public function setSlowCommandThreshold(?float $threshold): void
    {
        if ($this->temporaryCommandThreshold === null) {
            $this->temporaryCommandThreshold = new TemporaryThreshold($threshold, null);
        }
    }

    public function restoreSlowCommandThreshold(): void
    {
        $this->temporaryCommandThreshold = null;
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
     * @param float|null $threshold threshold value in milliseconds
     */
    public function setSlowDbTransactionThreshold(?float $threshold): TemporaryThreshold
    {
        $temporaryThreshold = self::getTemporaryThreshold($this->temporaryDbTransactionThreshold);
        if ($temporaryThreshold !== null) {
            return new TemporaryThreshold($threshold, $temporaryThreshold->getValue());
        }

        $temporaryThreshold = new TemporaryThreshold($threshold, $this->getSlowDbTransactionThreshold());
        $this->temporaryDbTransactionThreshold = \WeakReference::create($temporaryThreshold);

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

    /**
     * @param non-empty-string $key
     */
    private function getConfigValue(string $key): Rules
    {
        return Validator::make($this->config->get($key), 'Config key: '.$key);
    }
}
