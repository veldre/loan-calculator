<?php
declare(strict_types=1);

namespace App\Loan;

use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

class LinearLoan implements LoanInterface
{
    public function __construct(private Money $principal, private LoanTerm $term, private Apr $apr)
    {
    }

    // Returns the first payment as for linear loans monthly payment is not constant
    public function getMonthlyPayment(): Money
    {
        $principalCents = $this->principal->cents();
        $months = $this->term->months();
        $monthlyRate = $this->apr->monthlyRate();
        $monthlyPrincipalCents = (int) round($principalCents / $months);

        if ($this->apr->isZero()) {
            return Money::fromCents($monthlyPrincipalCents);
        }

        $interestCents = (int) round($principalCents * $monthlyRate);

        return Money::fromCents($monthlyPrincipalCents + $interestCents);
    }

    public function getTotalRepayment(): Money
    {
        $totalCents = 0;
        $schedule = $this->getAmortizationSchedule();

        foreach ($schedule as $row) {
            $totalCents += $row['payment']->cents();
        }

        return Money::fromCents($totalCents);
    }

    public function getTotalInterest(): Money
    {
        $totalCents = 0;
        $schedule = $this->getAmortizationSchedule();

        foreach ($schedule as $row) {
            $totalCents += $row['interest']->cents();
        }

        return Money::fromCents($totalCents);
    }

    public function getAmortizationSchedule(): array
    {
        $schedule = [];

        $balanceCents = $this->principal->cents();
        $months = $this->term->months();
        $monthlyRate = $this->apr->monthlyRate();
        $monthlyPrincipalCents = (int) round($balanceCents / $months);

        for ($month = 1; $month <= $months; $month++) {

            $interestCents = $this->apr->isZero()? 0 : (int) round($balanceCents * $monthlyRate);

            if ($month === $months) {
                $principalCents = $balanceCents;
            } else {
                $principalCents = $monthlyPrincipalCents;
            }

            $paymentCents = $principalCents + $interestCents;
            $balanceCents -= $principalCents;

            $schedule[] = [
                'month' => $month,
                'payment' => Money::fromCents($paymentCents),
                'interest' => Money::fromCents($interestCents),
                'principal' => Money::fromCents($principalCents),
                'balance' => Money::fromCents($balanceCents),
            ];
        }

        return $schedule;
    }
}
