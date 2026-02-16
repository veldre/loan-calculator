<?php

namespace App\Loan;

interface LoanInterface
{
    public function getMonthlyPayment(): float;

    public function getTotalRepayment(): float;

    public function getTotalInterest(): float;

    public function getAmortizationSchedule(): array;
}
