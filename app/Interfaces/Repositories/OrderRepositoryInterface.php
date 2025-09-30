<?php

namespace App\Interfaces\Repositories;

use App\Models\{
    Order,
    OrderItem,
};
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function getAllOrdersByStatus(int $userId, int $perPage = 15, ?string $status = null): LengthAwarePaginator;
    public function store(array $data): Order;
    public function createItem(Order $order, array $itemData): OrderItem;
    public function updateTotal(Order $order, float $totalAmount): void;
    public function loadRelations(Order $order): Order;
    public function update(Order $order, array $data): bool;
    public function updateItem(Order $order, array $itemsData): bool;
    public function updateStatus(Order $order, OrderStatus $status): bool;
    public function delete(Order $order): bool;
    public function hasPayments(Order $order): bool;
}


