<?php

namespace App\Loan;

use App\Exceptions\InvalidLoanException;

class LinearLoan implements LoanInterface
{
    public function __construct(private float $principal, private int $months, private float $apr)
    {
        $this->assertValid();
    }

    // Returns the first payment as for linear loans monthly payment is not constant
    public function getMonthlyPayment(): float
    {
        $monthlyRate = $this->apr / 100 / 12;

        $principalPart = round($this->principal / $this->months, 2);

        if ($this->apr === 0.0) {
            return $principalPart;
        }

        $interest = round($this->principal * $monthlyRate, 2);

        return round($principalPart + $interest, 2);
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
        $monthlyRate = $this->apr / 100 / 12;
        $principalPart = round($this->principal / $this->months, 2);

        for ($month = 1; $month <= $this->months; $month++) {

            if ($this->apr === 0.0) {
                $interest = 0.0;
            } else {
                $interest = round($balance * $monthlyRate, 2);
            }

            if ($month === $this->months) {
                $principal = $balance;
            } else {
                $principal = $principalPart;
            }

            $payment = round($principal + $interest, 2);
            $balance = round($balance - $principal, 2);

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
