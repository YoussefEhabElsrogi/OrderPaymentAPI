<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class PayPalPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(array $data): array
    {
        // Simulate PayPal payment processing
        $this->validateData($data);

        // Simulate API call to PayPal
        $success = $this->simulatePayPalApiCall($data);

        if ($success) {
            return [
                'status' => PaymentStatus::COMPLETED,
                'transaction_id' => 'PP_' . uniqid(),
                'gateway_response' => [
                    'paypal_transaction_id' => 'PP_' . uniqid(),
                    'status' => 'completed',
                    'amount' => $data['amount'],
                    'currency' => 'USD',
                    'timestamp' => now()->toDateTimeString(),
                ],
                'message' => 'Payment processed successfully via PayPal',
            ];
        }

        return [
            'status' => PaymentStatus::FAILED,
            'transaction_id' => null,
            'gateway_response' => [
                'error' => 'Payment failed',
                'reason' => 'Insufficient funds or invalid PayPal account',
                'timestamp' => now()->toDateTimeString(),
            ],
            'message' => 'Payment failed via PayPal',
        ];
    }

    public function getPaymentMethod(): string
    {
        return PaymentMethod::PAYPAL->value;
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['paypal_email'])) {
            $errors['paypal_email'] = ['PayPal email is required'];
        } elseif (!filter_var($data['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['paypal_email'] = ['Invalid PayPal email format'];
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = ['Amount must be greater than 0'];
        }

        return $errors;
    }

    private function simulatePayPalApiCall(array $data): bool
    {
        // Simulate 90% success rate for demo purposes
        return rand(1, 10) <= 9;
    }
}
