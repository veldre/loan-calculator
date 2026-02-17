<?php

namespace App\Loan;

use App\Exceptions\InvalidLoanException;

class LoanFactory
{
    public function create(string $type, float $principal, int $months, float $apr): LoanInterface
    {
        return match ($type) {
            'annuity' => new AnnuityLoan($principal, $months, $apr),
            'linear' => new LinearLoan($principal, $months, $apr),
            default => throw new InvalidLoanException('Unsupported loan type.')
        };
    }
}
