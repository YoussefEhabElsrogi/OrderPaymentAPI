<?php

namespace App\Interfaces\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentServiceInterface
{
    /**
     * Get all payments with pagination for a specific user
     */
    public function getAllPaymentsWithUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Process payment for an order
     */
    public function processPayment(Order $order, string $paymentMethod, array $paymentData): Payment|bool;

    /**
     * Get payments for a specific order
     */
    public function getPaymentsForOrder(Order $order): Collection;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, PaymentStatus $status): Payment;

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Check if payment method is supported
     */
    public function isPaymentMethodSupported(string $paymentMethod): bool;

    /**
     * Load relations for a payment
     */
    public function loadRelations(Payment $payment): Payment;
}
