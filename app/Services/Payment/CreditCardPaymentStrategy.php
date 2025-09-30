<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class CreditCardPaymentStrategy implements PaymentStrategyInterface
{
    public function pay(array $data): array
    {
        // Simulate credit card payment processing
        $this->validateData($data);

        // Simulate API call to credit card processor
        $success = $this->simulateCreditCardApiCall($data);

        if ($success) {
            return [
                'status' => PaymentStatus::COMPLETED,
                'transaction_id' => 'CC_' . uniqid(),
                'gateway_response' => [
                    'card_transaction_id' => 'CC_' . uniqid(),
                    'status' => 'completed',
                    'amount' => $data['amount'],
                    'currency' => 'USD',
                    'card_last_four' => substr($data['card_number'], -4),
                    'card_type' => $this->detectCardType($data['card_number']),
                    'timestamp' => now()->toDateTimeString(),
                ],
                'message' => 'Payment processed successfully via Credit Card',
            ];
        }

        return [
            'status' => PaymentStatus::FAILED,
            'transaction_id' => null,
            'gateway_response' => [
                'error' => 'Payment failed',
                'reason' => 'Invalid card details or insufficient funds',
                'timestamp' => now()->toDateTimeString(),
            ],
            'message' => 'Payment failed via Credit Card',
        ];
    }

    public function getPaymentMethod(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['card_number'])) {
            $errors['card_number'] = ['Card number is required'];
        } elseif (!$this->validateCardNumber($data['card_number'])) {
            $errors['card_number'] = ['Invalid card number format'];
        }

        if (empty($data['expiry_month']) || empty($data['expiry_year'])) {
            $errors['expiry'] = ['Card expiry date is required'];
        } elseif (!$this->validateExpiry($data['expiry_month'], $data['expiry_year'])) {
            $errors['expiry'] = ['Card has expired'];
        }

        if (empty($data['cvv'])) {
            $errors['cvv'] = ['CVV is required'];
        } elseif (!preg_match('/^\d{3,4}$/', $data['cvv'])) {
            $errors['cvv'] = ['Invalid CVV format'];
        }

        if (empty($data['cardholder_name'])) {
            $errors['cardholder_name'] = ['Cardholder name is required'];
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = ['Amount must be greater than 0'];
        }

        return $errors;
    }

    private function simulateCreditCardApiCall(array $data): bool
    {
        // Simulate 95% success rate for demo purposes
        // You can adjust this for testing
        return rand(1, 100) <= 95;
    }

    private function validateCardNumber(string $cardNumber): bool
    {
        // Remove spaces and dashes
        $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);

        // Check if it's numeric and has valid length
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }

        // Luhn algorithm validation
        $sum = 0;
        $length = strlen($cardNumber);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $cardNumber[$length - $i - 1];

            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    private function validateExpiry(int $month, int $year): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($year < $currentYear) {
            return false;
        }

        if ($year === $currentYear && $month < $currentMonth) {
            return false;
        }

        return $month >= 1 && $month <= 12;
    }

    private function detectCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);

        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6/', $cardNumber)) {
            return 'Discover';
        }

        return 'Unknown';
    }
}
