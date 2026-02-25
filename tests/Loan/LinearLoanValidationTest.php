<?php

namespace Tests\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\LinearLoan;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class LinearLoanValidationTest extends TestCase
{
    public function test_principal_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanException::class);

        new LinearLoan(Money::fromFloat(0), new LoanTerm(12), new Apr(5));
    }

    public function test_months_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanException::class);

        new LinearLoan(Money::fromFloat(1000), new LoanTerm(0), new Apr(5));
    }

    public function test_apr_cannot_be_negative(): void
    {
        $this->expectException(InvalidLoanException::class);

        new LinearLoan(Money::fromFloat(1000), new LoanTerm(12), new Apr(-1));
    }
}
