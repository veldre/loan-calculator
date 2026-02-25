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

        $this->assertGreaterThan($schedule[count($schedule) - 1]['payment'], $schedule[0]['payment']);
    }

    public function test_payments_decrease_over_time(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        for ($i = 1; $i < count($schedule); $i++) {
            $this->assertLessThan($schedule[$i - 1]['payment'], $schedule[$i]['payment']);
        }
    }

    public function test_principal_portion_is_constant(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $firstPrincipal = $schedule[0]['principal'];

        for ($i = 1; $i < count($schedule) - 1; $i++) {
            $this->assertEquals($firstPrincipal, $schedule[$i]['principal']);
        }
    }

    public function test_zero_interest_loan_has_no_interest(): void
    {
        $this->assertEquals(0.00, $this->zeroInterestLoan->getTotalInterest());
    }

    public function test_total_principal_paid_equals_original_principal(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $principal = array_column($schedule, 'principal');

        $this->assertEquals(self::STANDARD_LOAN_PRINCIPAL, round(array_sum($principal), 2));
    }

    public function test_zero_interest_payments_equal_principal_portion(): void
    {
        $schedule = $this->zeroInterestLoan->getAmortizationSchedule();

        $expectedPrincipal = round(self::ZERO_INTEREST_LOAN_PRINCIPAL / self::ZERO_INTEREST_LOAN_MONTHS, 2);

        foreach ($schedule as $row) {
            $this->assertEquals($expectedPrincipal, $row['payment']);
        }
    }

    public function test_last_month_balance_is_zero(): void
    {
        $schedule = $this->standardLoan->getAmortizationSchedule();

        $last = $schedule[count($schedule) - 1];

        $this->assertEquals(0.00, $last['balance']);
    }
}
