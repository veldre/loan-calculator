<?php

namespace Tests\Loan;

use App\Loan\AnnuityLoan;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;
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
        $this->standardLoan = new AnnuityLoan(Money::fromFloat(self::STANDARD_LOAN_PRINCIPAL), new LoanTerm(self::STANDARD_LOAN_MONTHS), new Apr(self::STANDARD_LOAN_APR));
        $this->zeroInterestLoan = new AnnuityLoan(Money::fromFloat(self::ZERO_INTEREST_LOAN_PRINCIPAL), new LoanTerm(self::ZERO_INTEREST_LOAN_MONTHS), new Apr(self::ZERO_INTEREST_LOAN_APR));
    }

    public function test_monthly_payment_for_standard_annuity_loan(): void
    {
        $this->assertSame(53682, $this->standardLoan->getMonthlyPayment()->cents());
    }

    public function test_monthly_payment_when_interest_is_zero(): void
    {
        $this->assertSame(50000, $this->zeroInterestLoan->getMonthlyPayment()->cents());
    }

    public function test_total_repayment_for_standard_annuity_loan(): void
    {
        $this->assertSame(
            self::STANDARD_LOAN_PRINCIPAL * 100 + $this->standardLoan->getTotalInterest()->cents(),
            $this->standardLoan->getTotalRepayment()->cents()
        );
    }

    public function test_zero_interest_loan_has_no_interest(): void
    {
        $this->assertSame(0, $this->zeroInterestLoan->getTotalInterest()->cents());
    }

    public function test_total_repayment_equals_principal_when_interest_is_zero(): void
    {
        $this->assertSame(self::ZERO_INTEREST_LOAN_PRINCIPAL * 100, $this->zeroInterestLoan->getTotalRepayment()->cents());
    }

    public function test_total_principal_paid_equals_original_principal(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $totalPrincipalCents = 0;

        foreach ($schedule as $row) {
            $totalPrincipalCents += $row['principal']->cents();
        }

        $this->assertSame(self::STANDARD_LOAN_PRINCIPAL * 100,   $totalPrincipalCents);
    }

    public function test_total_interest_equals_sum_of_schedule_interest(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $totalInterestCents = 0;

        foreach ($schedule as $row) {
            $totalInterestCents += $row['interest']->cents();
        }

        $this->assertSame($totalInterestCents, $this->standardLoan->getTotalInterest()->cents());
    }

    public function test_first_month_of_amortization_schedule(): void
    {
        $loan = new AnnuityLoan(Money::fromFloat(1000), new LoanTerm(12), new Apr(5));

        $schedule = $loan->getAmortizationSchedule();

        $this->assertSame(1, $schedule[0]['month']);
        $this->assertSame(417, $schedule[0]['interest']->cents());
        $this->assertSame(8144, $schedule[0]['principal']->cents());
    }

    public function test_last_month_balance_is_zero(): void
    {
        $loan = new AnnuityLoan(Money::fromFloat(6000), new LoanTerm(12), new Apr(5));

        $schedule = $loan->getAmortizationSchedule();

        $lastMonth = $schedule[count($schedule) - 1];

        $this->assertSame(0, $lastMonth['balance']->cents());
    }

    public function test_schedule_contains_correct_number_of_months(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $this->assertCount(self::STANDARD_LOAN_MONTHS, $schedule);
    }
}
