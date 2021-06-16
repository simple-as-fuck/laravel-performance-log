<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Service;

use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;

class Stopwatch
{
    public function startMeasurement(?string $prefix = null): Measurement
    {
        $measurement = new Measurement();
        $measurement->start($prefix);
        return $measurement;
    }

    public function start(Measurement $measurement, ?string $prefix = null): void
    {
        $measurement->start($prefix);
    }

    /**
     * @param float $threshold threshold in milliseconds define to slow finish
     * @param callable|null $toSlowCallback what happened after slow finish
     * @return float measured time in milliseconds
     */
    public function check(Measurement $measurement, float $threshold, callable $toSlowCallback = null): float
    {
        return $this->checkPrefix($measurement, $threshold, null, $toSlowCallback);
    }

    /**
     * @param float $threshold threshold in milliseconds define to slow finish
     * @param callable|null $toSlowCallback what happened after slow finish
     * @return float measured time in milliseconds
     */
    public function checkPrefix(Measurement $measurement, float $threshold, ?string $prefix, callable $toSlowCallback = null): float
    {
        $time = $measurement->finish($prefix);
        if ($time >= $threshold) {
            if ($toSlowCallback) {
                $toSlowCallback($time);
            }
        }

        return $time;
    }
}
