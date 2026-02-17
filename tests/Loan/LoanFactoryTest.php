<?php

namespace Tests\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\AnnuityLoan;
use App\Loan\LinearLoan;
use App\Loan\LoanFactory;
use PHPUnit\Framework\TestCase;

class LoanFactoryTest extends TestCase
{
    private LoanFactory $factory;


    public function setUp(): void
    {
        $this->factory = new LoanFactory();
    }

    public function test_it_creates_annuity_loan(): void
    {
        $loan = $this->factory->create('annuity', 10000, 12, 5);

        $this->assertInstanceOf(AnnuityLoan::class, $loan);
    }

    public function test_it_creates_linear_loan(): void
    {
        $loan = $this->factory->create('linear', 10000, 12, 5);

        $this->assertInstanceOf(LinearLoan::class, $loan);
    }

    public function test_it_throws_exception_for_unknown_type(): void
    {
        $this->expectException(InvalidLoanException::class);

        $this->factory->create('something', 10000, 12, 5);
    }
}
