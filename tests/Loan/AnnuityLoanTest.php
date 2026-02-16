<?php

namespace Tests\Loan;

use App\Loan\AnnuityLoan;
use PHPUnit\Framework\TestCase;

class AnnuityLoanTest extends TestCase
{
    private const STANDARD_LOAN_PRINCIPAL = 100000;
    private const STANDARD_LOAN_MONTHS = 360;
    private const STANDARD_LOAN_APR = 5;

    private const ZERO_INTEREST_LOAN_PRINCIPAL = 12000;
    private const ZERO_INTEREST_LOAN_MONTHS = 24;
    private const ZERO_INTEREST_LOAN_APR = 0;

    private AnnuityLoan $standardLoan;
    private AnnuityLoan $zeroInterestLoan;
    
    
    public function setUp(): void
    {
        $this->standardLoan = new AnnuityLoan(self::STANDARD_LOAN_PRINCIPAL, self::STANDARD_LOAN_MONTHS, self::STANDARD_LOAN_APR);
        $this->zeroInterestLoan = new AnnuityLoan(self::ZERO_INTEREST_LOAN_PRINCIPAL, self::ZERO_INTEREST_LOAN_MONTHS, self::ZERO_INTEREST_LOAN_APR);
    }

    public function test_monthly_payment_for_standard_annuity_loan(): void
    {
        $this->assertEquals(536.82, $this->standardLoan->getMonthlyPayment());
    }

    public function test_monthly_payment_when_interest_is_zero(): void
    {
        $this->assertEquals(500.00, $this->zeroInterestLoan->getMonthlyPayment());
    }

    public function test_total_repayment_for_standard_annuity_loan(): void
    {
        $this->assertEquals(
            round(self::STANDARD_LOAN_PRINCIPAL + $this->standardLoan->getTotalInterest(), 2),
            round($this->standardLoan->getTotalRepayment(), 2)
        );
    }

    public function test_zero_interest_loan_has_no_interest(): void
    {
        $this->assertEquals(0.00, $this->zeroInterestLoan->getTotalInterest());
    }

    public function test_total_repayment_equals_principal_when_interest_is_zero(): void
    {
        $this->assertEquals(self::ZERO_INTEREST_LOAN_PRINCIPAL, $this->zeroInterestLoan->getTotalRepayment());
    }

    public function test_total_interest_equals_sum_of_schedule_interest(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $interest = array_column($schedule, 'interest');

        $expected = round(array_sum($interest), 2);

        $this->assertEquals($expected, $this->standardLoan->getTotalInterest());
    }

    public function test_first_month_of_amortization_schedule(): void
    {
        $loan = new AnnuityLoan(1000, 12, 5);

        $schedule = $loan->getAmortizationSchedule();

        $this->assertEquals(1, $schedule[0]['month']);
        $this->assertEquals(4.17, $schedule[0]['interest']);
        $this->assertEquals(81.44, $schedule[0]['principal']);
    }

    public function test_last_month_balance_is_zero(): void
    {
        $loan = new AnnuityLoan(6000, 12, 5);

        $schedule = $loan->getAmortizationSchedule();

        $lastMonth = $schedule[count($schedule) - 1];

        $this->assertEquals(0.00, $lastMonth['balance']);
    }

    public function test_schedule_contains_correct_number_of_months(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $this->assertCount(self::STANDARD_LOAN_MONTHS, $schedule);
    }
}
