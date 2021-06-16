<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Model;

final class Measurement
{
    /** @var array<string, float> */
    private array $startsAt;

    public function __construct()
    {
        $this->startsAt = [];
    }

    public function start(?string $prefix): void
    {
        $this->startsAt[(string) $prefix] = microtime(true);
    }

    public function running(?string $prefix): bool
    {
        return array_key_exists((string) $prefix, $this->startsAt);
    }

    /**
     * @return float measured time in milliseconds
     */
    public function finish(?string $prefix): float
    {
        if (! $this->running($prefix)) {
            return 0;
        }

        $measuredTime = (microtime(true) - $this->startsAt[(string) $prefix]) * 1000;
        unset($this->startsAt[(string) $prefix]);
        return $measuredTime;
    }
}
