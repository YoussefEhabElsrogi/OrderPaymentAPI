<?php

namespace App\Services\Payment;

interface PaymentStrategyInterface
{
    /**
     * Process payment with the given data
     */
    public function pay(array $data): array;

    /**
     * Get the payment method this strategy handles
     */
    public function getPaymentMethod(): string;

    /**
     * Validate payment data specific to this strategy
     */
    public function validateData(array $data): array;
}
