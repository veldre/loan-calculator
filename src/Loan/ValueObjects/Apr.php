<?php
declare(strict_types=1);

namespace App\Loan\ValueObjects;

use App\Exceptions\InvalidRateException;


final class Apr
{
    private float $rate;

    public function __construct(float $rate)
    {
        if ($rate < 0) {
            throw new InvalidRateException('APR cannot be negative.');
        }

        $this->rate = $rate;
    }

    public function percentage(): float
    {
        return $this->rate;
    }

    public function monthlyRate(): float
    {
        return $this->rate / 100 / 12;
    }

    public function isZero(): bool
    {
        return $this->rate === 0.0;
    }
}
