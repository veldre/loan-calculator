<?php
declare(strict_types=1);

namespace App\Loan\ValueObjects;

use App\Exceptions\InvalidMoneyException;


final class Money
{
    private int $amountInCents;

    public function __construct(int $amountInCents)
    {
        if ($amountInCents < 0) {
            throw new InvalidMoneyException('Money cannot be negative.');
        }

        $this->amountInCents = $amountInCents;
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function cents(): int
    {
        return $this->amountInCents;
    }

    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }
}
