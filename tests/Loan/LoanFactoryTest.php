<?php

namespace Tests\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\AnnuityLoan;
use App\Loan\LinearLoan;
use App\Loan\LoanFactory;
use App\Loan\LoanType;
use PHPUnit\Framework\TestCase;

class LoanFactoryTest extends TestCase
{
    private LoanFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new LoanFactory();
    }

    public function test_it_creates_annuity_loan(): void
    {
        $loan = $this->factory->create(LoanType::ANNUITY, 10000, 12, 5);

        $this->assertInstanceOf(AnnuityLoan::class, $loan);
    }

    public function test_it_creates_linear_loan(): void
    {
        $loan = $this->factory->create(LoanType::LINEAR, 10000, 12, 5);

        $this->assertInstanceOf(LinearLoan::class, $loan);
    }

    public function test_principal_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanException::class);

        $this->factory->create(LoanType::ANNUITY, 0.0, 12, 5);
    }
}