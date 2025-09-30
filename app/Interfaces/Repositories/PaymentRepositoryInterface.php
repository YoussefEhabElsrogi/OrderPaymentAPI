<?php

namespace App\Interfaces\Repositories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    /**
     * Get all payments for a specific user with pagination
     */
    public function getAllPaymentsWithUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new payment
     */
    public function create(array $data): Payment;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, PaymentStatus $status): bool;

    /**
     * Get payments for a specific order
     */
    public function getPaymentsForOrder(Order $order): Collection;

    /**
     * Get payments for a specific user and order
     */
    public function loadRelations(Payment $payment): Payment;
}
