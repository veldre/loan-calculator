<?php

namespace Tests\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\AnnuityLoan;
use PHPUnit\Framework\TestCase;

class AnnuityLoanValidationTest extends TestCase
{
    public function test_principal_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanException::class);

        new AnnuityLoan(0, 12, 5);
    }

    public function test_months_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanException::class);

        new AnnuityLoan(1000, 0, 5);
    }

    public function test_apr_cannot_be_negative(): void
    {
        $this->expectException(InvalidLoanException::class);

        new AnnuityLoan(1000, 12, -1);
    }
}
