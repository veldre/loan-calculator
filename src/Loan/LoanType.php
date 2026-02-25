<?php

namespace App\Loan;

enum LoanType: string
{
    case ANNUITY = 'annuity';
    case LINEAR = 'linear';
}
