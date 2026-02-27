<?php
declare(strict_types=1);

namespace App\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

final class LoanFactory
{
    public function create(LoanType $type, float $principal, int $months, float $apr): LoanInterface
    {
        $principalMoney = Money::fromFloat($principal);

        if ($principalMoney->cents() === 0) {
            throw new InvalidLoanException('Principal must be greater than 0.');
        }

        $term = new LoanTerm($months);
        $rate = new Apr($apr);

        return match ($type) {
            LoanType::ANNUITY => new AnnuityLoan($principalMoney, $term, $rate),
            LoanType::LINEAR => new LinearLoan($principalMoney, $term, $rate),
        };
    }
}
