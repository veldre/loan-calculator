<?php
declare(strict_types=1);

namespace App\Loan;

use App\Exceptions\InvalidLoanException;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

class AnnuityLoan implements LoanInterface
{
    public function __construct(private Money $principal, private LoanTerm $term, private Apr $apr)
    {
        if ($this->principal->cents() === 0) {
            throw new InvalidLoanException('Principal must be greater than 0.');
        }
    }

    public function getMonthlyPayment(): float
    {
        if ($this->apr->isZero()) {
            return round($this->principal->toFloat() / $this->term->months(), 2);
        }

        $monthlyRate = $this->apr->monthlyRate();
        $growthFactor = 1 + $monthlyRate;
        $discountFactor = pow($growthFactor, -$this->term->months());
        $annuityFactor = $monthlyRate / (1 - $discountFactor);
        $payment = $this->principal->toFloat() * $annuityFactor;

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

        $balance = $this->principal->toFloat();
        $monthlyPayment = $this->getMonthlyPayment();
        $monthlyRate = $this->apr->monthlyRate();

        for ($month = 1; $month <= $this->term->months(); $month++) {

            if ($this->apr->isZero()) {
                $interest = 0.0;
            } else {
                $interest = round($balance * $monthlyRate, 2);
            }

            if ($month === $this->term->months()) {
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
}
