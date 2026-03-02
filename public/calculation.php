<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Exceptions\InvalidLoanException;
use App\Loan\LoanFactory;
use App\Loan\LoanType;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (! is_array($data)) {
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

if (empty($data['type']) || $data['principal'] === '' || $data['months'] === '' || $data['apr'] === '') {
    echo json_encode(['error' => 'Please fill all required fields.']);
    exit;
}

if (! is_numeric($data['principal']) || ! is_numeric($data['months']) || ! is_numeric($data['apr'])) {
    echo json_encode(['error' => 'Invalid numeric input.']);
    exit;
}

try {
    $factory = new LoanFactory();

    $type = LoanType::tryFrom($data['type']);

    if (! $type) {
        echo json_encode(['error' => 'Invalid loan type.']);
        exit;
    }

    $loan = $factory->create($type, (float) $data['principal'], (int) $data['months'], (float) $data['apr']);

    $schedule = array_map(function ($row) {
        return [
            'month' => $row['month'],
            'payment' => $row['payment']->toFloat(),
            'interest' => $row['interest']->toFloat(),
            'principal' => $row['principal']->toFloat(),
            'balance' => $row['balance']->toFloat(),
        ];
    }, $loan->getAmortizationSchedule());

    echo json_encode([
        'monthlyPayment' => $loan->getMonthlyPayment()->toFloat(),
        'totalInterest' => $loan->getTotalInterest()->toFloat(),
        'totalRepayment' => $loan->getTotalRepayment()->toFloat(),
        'schedule' => $schedule,
    ]);

} catch (InvalidLoanException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Throwable $e) {
    echo json_encode(['error' => 'Unexpected error.']);
}
