<?php

namespace App\Loan;

use App\Exceptions\InvalidLoanException;

class AnnuityLoan implements LoanInterface
{
    public function __construct(private float $principal, private int $months, private float $apr)
    {
        $this->assertValid();
    }

    public function getMonthlyPayment(): float
    {
        if ($this->apr === 0.0) {
            return round($this->principal / $this->months, 2);
        }

        $monthlyRate = $this->apr / 100 / 12;
        $growthFactor = 1 + $monthlyRate;
        $discountFactor = pow($growthFactor, -$this->months);
        $annuityFactor = $monthlyRate / (1 - $discountFactor);
        $payment = $this->principal * $annuityFactor;

        return round($payment, 2);
    }

    public function getTotalRepayment(): float
    {
        $payments = array_column($this->getAmortizationSchedule(), 'payment');

        return round(array_sum($payments), 2);
    }

    public function getTotalInterest(): float
    {
        $interest = array_column($this->getAmortizationSchedule(), 'interest');

        return round(array_sum($interest), 2);
    }

    public function getAmortizationSchedule(): array
    {
        $schedule = [];

        $balance = $this->principal;
        $monthlyPayment = $this->getMonthlyPayment();
        $monthlyRate = $this->apr / 100 / 12;

        for ($month = 1; $month <= $this->months; $month++) {

            if ($this->apr === 0.0) {
                $interest = 0.0;
            } else {
                $interest = round($balance * $monthlyRate, 2);
            }

            if ($month === $this->months) {
                // last month adjustment because of rounding discrepancies
                $principal = $balance;
                $payment = round($principal + $interest, 2);
                $balance = 0.0;
            } else {
                $payment = $monthlyPayment;
                $principal = round($payment - $interest, 2);
                $balance = round($balance - $principal, 2);
            }

            $schedule[] = [
                'month' => $month,
                'payment' => $payment,
                'interest' => $interest,
                'principal' => $principal,
                'balance' => $balance,
            ];
        }

        return $schedule;
    }

    private function assertValid(): void
    {
        if ($this->principal <= 0) {
            throw new InvalidLoanException('Principal must be greater than 0.');
        }

        if ($this->months <= 0) {
            throw new InvalidLoanException('Loan term must be greater than 0.');
        }

        if ($this->apr < 0) {
            throw new InvalidLoanException('APR cannot be negative.');
        }
    }
}
