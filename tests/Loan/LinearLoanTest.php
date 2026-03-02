<?php

namespace Tests\Loan;

use App\Loan\LinearLoan;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class LinearLoanTest extends TestCase
{
    private const STANDARD_LOAN_PRINCIPAL = 100000;
    private const STANDARD_LOAN_MONTHS = 360;
    private const STANDARD_LOAN_APR = 5;

    private const ZERO_INTEREST_LOAN_PRINCIPAL = 12000;
    private const ZERO_INTEREST_LOAN_MONTHS = 24;
    private const ZERO_INTEREST_LOAN_APR = 0;

    private LinearLoan $standardLoan;
    private LinearLoan $zeroInterestLoan;


    public function setUp(): void
    {
        $this->standardLoan = new LinearLoan(Money::fromFloat(self::STANDARD_LOAN_PRINCIPAL), new LoanTerm(self::STANDARD_LOAN_MONTHS), new Apr(self::STANDARD_LOAN_APR));
        $this->zeroInterestLoan = new LinearLoan(Money::fromFloat(self::ZERO_INTEREST_LOAN_PRINCIPAL), new LoanTerm(self::ZERO_INTEREST_LOAN_MONTHS), new Apr(self::ZERO_INTEREST_LOAN_APR));
    }

    public function test_first_payment_is_greater_than_last_payment(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $this->assertGreaterThan($schedule[count($schedule) - 1]['payment']->cents(), $schedule[0]['payment']->cents());
    }

    public function test_payments_decrease_over_time(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        for ($i = 1; $i < count($schedule); $i++) {
            $this->assertLessThan($schedule[$i - 1]['payment']->cents(), $schedule[$i]['payment']->cents());
        }
    }

    public function test_principal_portion_is_constant(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $firstPrincipal = $schedule[0]['principal']->cents();

        for ($i = 1; $i < count($schedule) - 1; $i++) {
            $this->assertSame($firstPrincipal, $schedule[$i]['principal']->cents());
        }
    }

    public function test_zero_interest_loan_has_no_interest(): void
    {
        $this->assertSame(0, $this->zeroInterestLoan->getTotalInterest()->cents());
    }

    public function test_total_principal_paid_equals_original_principal(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $totalPrincipalCents = 0;

        foreach ($schedule as $row) {
            $totalPrincipalCents += $row['principal']->cents();
        }

        $this->assertSame(self::STANDARD_LOAN_PRINCIPAL * 100, $totalPrincipalCents);
    }

    public function test_zero_interest_payments_equal_principal_portion(): void
    {
        $schedule = $this->zeroInterestLoan->getAmortizationSchedule();

        $expectedPrincipalCents = (int) round((self::ZERO_INTEREST_LOAN_PRINCIPAL * 100) / self::ZERO_INTEREST_LOAN_MONTHS);

        foreach ($schedule as $row) {
            $this->assertSame($expectedPrincipalCents, $row['payment']->cents());
        }
    }

    public function test_last_month_balance_is_zero(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $last = $schedule[count($schedule) - 1];

        $this->assertSame(0, $last['balance']->cents());
    }
}
