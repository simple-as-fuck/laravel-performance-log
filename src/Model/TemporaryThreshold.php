<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Model;

final class TemporaryThreshold
{
    private ?float $value;
    private ?float $original;

    public function __construct(?float $value, ?float $original)
    {
        $this->value = $value;
        $this->original = $original;
    }

    /**
     * @return float|null threshold value in milliseconds
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    public function isRestored(): bool
    {
        return $this->value === $this->original;
    }

    public function restore(): void
    {
        $this->value = $this->original;
    }

    public function __destruct()
    {
        $this->restore();
    }
}
