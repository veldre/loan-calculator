<?php
declare(strict_types=1);

namespace App\Loan;

use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

class LoanFactory
{
    public function create(LoanType $type, float $principal, int $months, float $apr): LoanInterface
    {
        return match ($type) {
            LoanType::ANNUITY => new AnnuityLoan(Money::fromFloat($principal), new LoanTerm($months), new Apr($apr)),
            LoanType::LINEAR => new LinearLoan(Money::fromFloat($principal), new LoanTerm($months), new Apr($apr)),
        };
    }
}
