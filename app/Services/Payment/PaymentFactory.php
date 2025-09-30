<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use InvalidArgumentException;

class PaymentFactory
{
    private array $strategies = [];

    public function __construct()
    {
        $this->registerDefaultStrategies();
    }

    /**
     * Register default payment strategies
     */
    private function registerDefaultStrategies(): void
    {
        $this->registerStrategy(new PayPalPaymentStrategy());
        $this->registerStrategy(new CreditCardPaymentStrategy());
        $this->registerStrategy(new BankTransferPaymentStrategy());
    }

    /**
     * Register a payment strategy
     */
    public function registerStrategy(PaymentStrategyInterface $strategy): void
    {
        $this->strategies[$strategy->getPaymentMethod()] = $strategy;
    }

    /**
     * Get payment strategy by method
     */
    public function getStrategy(string $paymentMethod): PaymentStrategyInterface
    {
        if (!isset($this->strategies[$paymentMethod])) {
            throw new InvalidArgumentException("Payment method '{$paymentMethod}' is not supported");
        }

        return $this->strategies[$paymentMethod];
    }

    /**
     * Get all available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return array_keys($this->strategies);
    }

    /**
     * Check if payment method is supported
     */
    public function isPaymentMethodSupported(string $paymentMethod): bool
    {
        return isset($this->strategies[$paymentMethod]);
    }

    /**
     * Process payment using the appropriate strategy
     */
    public function processPayment(string $paymentMethod, array $data): array
    {
        $strategy = $this->getStrategy($paymentMethod);
        return $strategy->pay($data);
    }

    /**
     * Validate payment data using the appropriate strategy
     */
    public function validatePaymentData(string $paymentMethod, array $data): array
    {
        $strategy = $this->getStrategy($paymentMethod);
        return $strategy->validateData($data);
    }
}
