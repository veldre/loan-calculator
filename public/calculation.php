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

    echo json_encode([
        'monthlyPayment' => $loan->getMonthlyPayment(),
        'totalInterest' => $loan->getTotalInterest(),
        'totalRepayment' => $loan->getTotalRepayment(),
        'schedule' => $loan->getAmortizationSchedule(),
    ]);

} catch (InvalidLoanException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Throwable $e) {
    echo json_encode(['error' => 'Unexpected error.']);
}
