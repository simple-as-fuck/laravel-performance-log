<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Service;

use Illuminate\Contracts\Config\Repository;
use SimpleAsFuck\PerformanceLog\Data\TemporaryThreshold;
use SimpleAsFuck\Validator\Factory\Validator;
use SimpleAsFuck\Validator\Rule\General\Rules;

/**
 * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
 */
class PerformanceLogConfig
{
    public function __construct(
        private readonly Repository $config,
        private readonly \SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig $performanceLogConfig,
    ) {
    }

    /**
     * @deprecated in composer package: simple-as-fuck/php-performance-log is removed
     */
    public function isDebugEnabled(): bool
    {
        return $this->getConfigValue('app.debug')->bool()->notNull();
    }

    /**
     * @deprecated in composer package: simple-as-fuck/php-performance-log is removed
     */
    public function getLogChannelName(): ?string
    {
        return $this->getConfigValue('performance_log.log_channel')->string()->nullable();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @return float|null threshold value in milliseconds
     */
    public function getSlowSqlQueryThreshold(): ?float
    {
        return $this->performanceLogConfig->getSlowSqlQueryThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @return float|null threshold value in milliseconds
     */
    public function getSlowDbTransactionThreshold(): ?float
    {
        return $this->performanceLogConfig->getSlowDbTransactionThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @return float|null threshold value in milliseconds
     */
    public function getSlowRequestThreshold(): ?float
    {
        return $this->performanceLogConfig->getSlowRequestThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @return float|null threshold value in seconds
     */
    public function getSlowCommandThreshold(): ?float
    {
        return $this->performanceLogConfig->getSlowCommandThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @return float|null threshold value in milliseconds
     */
    public function getSlowJobThreshold(): ?float
    {
        return $this->performanceLogConfig->getSlowJobThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @param float|null $threshold threshold value in seconds
     */
    public function setSlowCommandThreshold(?float $threshold): void
    {
        $this->performanceLogConfig->setSlowCommandThreshold($threshold);
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     */
    public function restoreSlowCommandThreshold(): void
    {
        $this->performanceLogConfig->restoreSlowCommandThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @param float|null $threshold threshold value in milliseconds
     */
    public function setSlowSqlQueryThreshold(?float $threshold): TemporaryThreshold
    {
        return $this->performanceLogConfig->setSlowSqlQueryThreshold($threshold);
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @param float|null $threshold threshold value in milliseconds
     */
    public function setSlowDbTransactionThreshold(?float $threshold): TemporaryThreshold
    {
        return $this->performanceLogConfig->setSlowDbTransactionThreshold($threshold);
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @param float|null $threshold value in milliseconds
     */
    public function setSlowRequestThreshold(?float $threshold): void
    {
        $this->performanceLogConfig->setSlowRequestThreshold($threshold);
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     * @param float|null $threshold value in milliseconds
     */
    public function setSlowJobThreshold(?float $threshold): void
    {
        $this->performanceLogConfig->setSlowJobThreshold($threshold);
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     */
    public function restoreSlowRequestThreshold(): void
    {
        $this->performanceLogConfig->restoreSlowRequestThreshold();
    }

    /**
     * @deprecated use SimpleAsFuck\PerformanceLog\Service\PerformanceLogConfig from composer package: simple-as-fuck/php-performance-log
     */
    public function restoreSlowJobThreshold(): void
    {
        $this->performanceLogConfig->restoreSlowJobThreshold();
    }

    /**
     * @param non-empty-string $key
     */
    private function getConfigValue(string $key): Rules
    {
        return Validator::make($this->config->get($key), 'Config key: '.$key);
    }
}
