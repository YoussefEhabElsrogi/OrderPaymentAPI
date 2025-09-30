<?php

namespace App\Interfaces\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    public function getOrdersByUserIdAndStatus(int $userId, int $perPage = 15, ?string $status = null): LengthAwarePaginator;
    public function store(array $orderData, array $itemsData): Order;
    public function loadRelations(Order $order): Order;

    public function updateOrder(Order $order, array $data, array $itemsData): Order;
    public function updateStatus(Order $order, OrderStatus $status): Order;
    public function delete(Order $order): bool;
    public function canAcceptPayments(Order $order): bool;
}
