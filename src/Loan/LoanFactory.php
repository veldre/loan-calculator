<?php

namespace App\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\LoanType;

class LoanFactory
{
    public function create(LoanType $type, float $principal, int $months, float $apr): LoanInterface
    {
        return match ($type) {
            LoanType::ANNUITY => new AnnuityLoan($principal, $months, $apr),
            LoanType::LINEAR => new LinearLoan($principal, $months, $apr),
            default => throw new InvalidLoanException('Unsupported loan type.')
        };
    }
}
