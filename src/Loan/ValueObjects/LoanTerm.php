<?php
declare(strict_types=1);

namespace App\Loan\ValueObjects;

use App\Exceptions\InvalidLoanTermException;

final class LoanTerm
{
    private int $months;

    public function __construct(int $months)
    {
        if ($months <= 0) {
            throw new InvalidLoanTermException('Loan term must be greater than 0.');
        }

        $this->months = $months;
    }

    public function months(): int
    {
        return $this->months;
    }
}