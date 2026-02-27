<?php

namespace App\Loan;

use App\Loan\ValueObjects\Money;

interface LoanInterface
{
    public function getMonthlyPayment(): Money;

    public function getTotalRepayment(): Money;

    public function getTotalInterest(): Money;

    public function getAmortizationSchedule(): array;
}
