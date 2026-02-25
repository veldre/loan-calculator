<?php
declare(strict_types=1);

namespace App\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

class LinearLoan implements LoanInterface
{
    public function __construct(private Money $principal, private LoanTerm $term, private Apr $apr)
    {
        if ($this->principal->cents() === 0) {
            throw new InvalidLoanException('Principal must be greater than 0.');
        }
    }

    // Returns the first payment as for linear loans monthly payment is not constant
    public function getMonthlyPayment(): float
    {
        $monthlyRate = $this->apr->monthlyRate();

        $principalPart = round($this->principal->toFloat() / $this->term->months(), 2);

        if ($this->apr->isZero()) {
            return $principalPart;
        }

        $interest = round($this->principal->toFloat() * $monthlyRate, 2);

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

        $balance = $this->principal->toFloat();
        $monthlyRate = $this->apr->monthlyRate();
        $principalPart = round($this->principal->toFloat() / $this->term->months(), 2);

        for ($month = 1; $month <= $this->term->months(); $month++) {

            if ($this->apr->isZero()) {
                $interest = 0.0;
            } else {
                $interest = round($balance * $monthlyRate, 2);
            }

            if ($month === $this->term->months()) {
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
}
