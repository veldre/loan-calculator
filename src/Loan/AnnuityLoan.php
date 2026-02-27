<?php
declare(strict_types=1);

namespace App\Loan;

use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;

class AnnuityLoan implements LoanInterface
{
    public function __construct(private Money $principal, private LoanTerm $term, private Apr $apr)
    {
    }

    public function getMonthlyPayment(): Money
    {
        $principalCents = $this->principal->cents();
        $months = $this->term->months();

        if ($this->apr->isZero()) {
            $monthlyCents = (int) round($principalCents / $months);

            return Money::fromCents($monthlyCents);
        }

        $monthlyRate = $this->apr->monthlyRate();

        $growthFactor = 1 + $monthlyRate;
        $discountFactor = pow($growthFactor, -$months);
        $annuityFactor = $monthlyRate / (1 - $discountFactor);

        $monthlyCents = (int) round($principalCents * $annuityFactor);

        return Money::fromCents($monthlyCents);
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
        $monthlyPaymentCents = $this->getMonthlyPayment()->cents();
        $monthlyRate = $this->apr->monthlyRate();
        $months = $this->term->months();

        for ($month = 1; $month <= $months; $month++) {

            $interestCents = $this->apr->isZero() ? 0 : (int) round($balanceCents * $monthlyRate);

            if ($month === $months) {
                $principalCents = $balanceCents;
                $paymentCents = $principalCents + $interestCents;
                $balanceCents = 0;
            } else {
                $paymentCents = $monthlyPaymentCents;
                $principalCents = $paymentCents - $interestCents;
                $balanceCents -= $principalCents;
            }

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
