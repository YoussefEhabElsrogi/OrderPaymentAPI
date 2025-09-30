<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\{
    Order,
    OrderItem,
};
use App\Interfaces\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAllOrdersByStatus(int $userId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $query = Order::with(['user', 'orderItems', 'payments'])->where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function loadRelations(Order $order): Order
    {
        return $order->load(['user', 'orderItems', 'payments']);
    }

    public function store(array $data): Order
    {
        return Order::create($data);
    }

    public function createItem(Order $order, array $itemData): OrderItem
    {
        return $order->orderItems()->create($itemData);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }
    public function updateItem(Order $order, array $itemsData): bool
    {
        return $order->orderItems()->update($itemsData);
    }

    public function updateTotal(Order $order, float $totalAmount): void
    {
        $order->update(['total_amount' => $totalAmount]);
    }

    public function updateStatus(Order $order, OrderStatus $status): bool
    {
        return $order->update(['status' => $status]);
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function hasPayments(Order $order): bool
    {
        return $order->hasPayments();
    }
}
