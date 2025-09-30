<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class BankTransferPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(array $data): array
    {
        // Simulate bank transfer payment processing
        $this->validateData($data);

        // Simulate API call to bank
        $success = $this->simulateBankTransferApiCall($data);

        if ($success) {
            return [
                'status' => PaymentStatus::COMPLETED,
                'transaction_id' => 'BT_' . uniqid(),
                'gateway_response' => [
                    'bank_transaction_id' => 'BT_' . uniqid(),
                    'status' => 'completed',
                    'amount' => $data['amount'],
                    'currency' => 'USD',
                    'bank_name' => $data['bank_name'] ?? 'Unknown Bank',
                    'account_number_masked' => '****' . substr($data['account_number'], -4),
                    'timestamp' => now()->toDateTimeString(),
                ],
                'message' => 'Payment processed successfully via Bank Transfer',
            ];
        }

        return [
            'status' => PaymentStatus::FAILED,
            'transaction_id' => null,
            'gateway_response' => [
                'error' => 'Payment failed',
                'reason' => 'Invalid bank details or insufficient funds',
                'timestamp' => now()->toDateTimeString(),
            ],
            'message' => 'Payment failed via Bank Transfer',
        ];
    }

    public function getPaymentMethod(): string
    {
        return PaymentMethod::BANK_TRANSFER->value;
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['account_number'])) {
            $errors['account_number'] = ['Account number is required'];
        } elseif (!preg_match('/^\d{8,20}$/', $data['account_number'])) {
            $errors['account_number'] = ['Invalid account number format'];
        }

        if (empty($data['routing_number'])) {
            $errors['routing_number'] = ['Routing number is required'];
        } elseif (!preg_match('/^\d{9}$/', $data['routing_number'])) {
            $errors['routing_number'] = ['Invalid routing number format'];
        }

        if (empty($data['account_holder_name'])) {
            $errors['account_holder_name'] = ['Account holder name is required'];
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = ['Amount must be greater than 0'];
        }

        return $errors;
    }

    private function simulateBankTransferApiCall(array $data): bool
    {
        // Simulate 95% success rate for demo purposes
        return rand(1, 10) <= 9.5;
    }
}
