<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Interfaces\Repositories\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function getAllPaymentsWithUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with(['order', 'order.user'])
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function getPaymentsForOrder(Order $order): Collection
    {
        return Payment::with(['order'])
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatePaymentStatus(Payment $payment, PaymentStatus $status): bool
    {
        return $payment->update(['status' => $status]);
    }

    public function loadRelations(Payment $payment): Payment
    {
        return $payment->load(['order.user', 'order.orderItems']);
    }
}
