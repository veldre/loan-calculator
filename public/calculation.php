<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Exceptions\InvalidLoanException;
use App\Loan\LoanFactory;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['type']) || $data['principal'] === '' || $data['months'] === '' || $data['apr'] === '') {
    echo json_encode(['error' => 'Please fill in all required fields.']);
    
    exit;
}

try {
    $factory = new LoanFactory();

    $loan = $factory->create($data['type'], (float) $data['principal'], (int) $data['months'], (float) $data['apr']);

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
