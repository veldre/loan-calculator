<?php

declare(strict_types=1);

namespace Tests\Loan;

use App\Exceptions\InvalidLoanTermException;
use App\Exceptions\InvalidMoneyException;
use App\Exceptions\InvalidRateException;
use App\Loan\ValueObjects\Apr;
use App\Loan\ValueObjects\LoanTerm;
use App\Loan\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class LoanValueObjectsValidationTest extends TestCase
{
    public function test_money_cannot_be_negative(): void
    {
        $this->expectException(InvalidMoneyException::class);

        Money::fromCents(-1);
    }

    public function test_months_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidLoanTermException::class);

        new LoanTerm(0);
    }

    public function test_apr_cannot_be_negative(): void
    {
        $this->expectException(InvalidRateException::class);

        new Apr(-1);
    }
}
