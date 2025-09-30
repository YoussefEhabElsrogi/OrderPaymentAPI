<?php

namespace App\Services;

use App\Enums\{
    OrderStatus,
    PaymentMethod,
    PaymentStatus,
};
use App\Models\Payment;
use App\Interfaces\Repositories\PaymentRepositoryInterface;
use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\Order;
use App\Services\Payment\PaymentFactory;
use App\Services\OrderService;
use App\Exceptions\PaymentException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private PaymentFactory $paymentFactory,
        private OrderService $orderService
    ) {
    }

    /**
     * Get all payments with pagination
     */
    public function getAllPaymentsWithUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->getAllPaymentsWithUser($userId, $perPage);
    }

    /**
     * Process payment for an order
     */
    public function processPayment(Order $order, string $paymentMethod, array $paymentData): Payment|bool
    {
        // Check if order can accept payments
        if (!$this->orderService->canAcceptPayments($order)) {
            throw new PaymentException('Order must be confirmed to accept payments');
        }


        // Add order amount to payment data
        $paymentData['amount'] = $order->total_amount;
        $paymentData['order_id'] = $order->id;
        $paymentData['payment_method'] = $paymentMethod;

        // Validate payment data
        $validationErrors = $this->paymentFactory->validatePaymentData($paymentMethod, $paymentData);
        if (!empty($validationErrors)) {
            throw new PaymentException('Payment validation failed: ' . json_encode($validationErrors));
        }

        // Process payment through the appropriate gateway
        $paymentResult = $this->paymentFactory->processPayment($paymentMethod, $paymentData);

        // Create payment record
        $paymentRecord = $this->paymentRepository->create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'status' => $paymentResult['status'],
            'amount' => $paymentData['amount'],
            'transaction_id' => $paymentResult['transaction_id'],
            'gateway_response' => $paymentResult['gateway_response'],
            'meta' => $this->extractMetaData($paymentMethod, $paymentData),
        ]);

        if ($paymentResult['status'] === PaymentStatus::FAILED) {
            throw new PaymentException('Payment failed: ' . $paymentResult['message'], 400);
        }

        // Load relationships for response
        $paymentRecord = $this->loadRelations($paymentRecord);

        return $paymentRecord;
    }

    /**
     * Get payments for a specific order
     */
    public function getPaymentsForOrder(Order $order): Collection
    {
        return $this->paymentRepository->getPaymentsForOrder($order);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, PaymentStatus $status): Payment
    {
        $this->paymentRepository->updatePaymentStatus($payment, $status);

        return $payment;
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->paymentFactory->getAvailablePaymentMethods();
    }

    /**
     * Check if payment method is supported
     */
    public function isPaymentMethodSupported(string $paymentMethod): bool
    {
        return $this->paymentFactory->isPaymentMethodSupported($paymentMethod);
    }

    /**
     * Load relations for a payment
     */
    public function loadRelations(Payment $payment): Payment
    {
        return $this->paymentRepository->loadRelations($payment);
    }

    /**
     * Extract metadata from payment data based on payment method
     */
    private function extractMetaData(string $paymentMethod, array $paymentData): array
    {
        $meta = [];

        switch ($paymentMethod) {
            case PaymentMethod::PAYPAL->value:
                $meta = [
                    'paypal_email' => $paymentData['paypal_email'] ?? null,
                ];
                break;

            case PaymentMethod::CREDIT_CARD->value:
                $meta = [
                    'cardholder_name' => $paymentData['cardholder_name'] ?? null,
                    'card_last_four' => isset($paymentData['card_number'])
                        ? substr($paymentData['card_number'], -4)
                        : null,
                    'expiry_month' => $paymentData['expiry_month'] ?? null,
                    'expiry_year' => $paymentData['expiry_year'] ?? null,
                ];
                break;

            case PaymentMethod::BANK_TRANSFER->value:
                $meta = [
                    'account_holder_name' => $paymentData['account_holder_name'] ?? null,
                    'account_number_masked' => isset($paymentData['account_number'])
                        ? '****' . substr($paymentData['account_number'], -4)
                        : null,
                    'routing_number' => $paymentData['routing_number'] ?? null,
                    'bank_name' => $paymentData['bank_name'] ?? null,
                ];
                break;
        }

        return $meta;
    }
}
