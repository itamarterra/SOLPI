<?php

declare(strict_types=1);

namespace SOLPI\Core\Http\Retry;

final class RetryPolicy
{
    private int $maxAttempts = 3;

    private int $delay = 1000;

    private float $backoff = 2.0;

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function delay(): int
    {
        return $this->delay;
    }

    public function backoff(): float
    {
        return $this->backoff;
    }

    public function setMaxAttempts(
        int $attempts
    ): self {

        $this->maxAttempts = max(1, $attempts);

        return $this;

    }

    public function setDelay(
        int $milliseconds
    ): self {

        $this->delay = max(0, $milliseconds);

        return $this;

    }

    public function setBackoff(
        float $factor
    ): self {

        $this->backoff = max(1.0, $factor);

        return $this;

    }

    public function calculateDelay(
        int $attempt
    ): int {

        return (int)(

            $this->delay *

            pow(

                $this->backoff,

                max(0, $attempt - 1)

            )

        );

    }
}