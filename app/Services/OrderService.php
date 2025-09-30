<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Services\OrderServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    public function getOrdersByUserIdAndStatus(int $userId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        return $this->orderRepository->getAllOrdersByStatus($userId, $perPage, $status);
    }

    public function store(array $data, array $itemsData): Order
    {
        // Create the order
        $order = $this->orderRepository->store($data);

        // Declare total amount
        $totalAmount = 0;

        // Create order items
        foreach ($itemsData as $itemData) {
            $this->orderRepository->createItem($order, $itemData);
            $totalAmount += $itemData['quantity'] * $itemData['price'];
        }

        // Update order total
        $this->orderRepository->updateTotal($order, $totalAmount);

        return $this->loadRelations($order);
    }

    public function loadRelations(Order $order): Order
    {
        return $this->orderRepository->loadRelations($order);
    }

    public function updateOrder(Order $order, array $data, array $itemsData = []): Order
    {
        // Update order
        $this->orderRepository->update($order, $data);

        // Declare total amount
        $totalAmount = 0;

        // Update order items
        if ($itemsData) {
            foreach ($itemsData as $itemData) {
                $this->orderRepository->updateItem($order, $itemData);
                $totalAmount += $itemData['quantity'] * $itemData['price'];
            }
        }

        // Update order total
        $this->orderRepository->updateTotal($order, $totalAmount);

        return $this->orderRepository->loadRelations($order);
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $this->orderRepository->updateStatus($order, $status);
        return $this->orderRepository->loadRelations($order);
    }

    public function delete(Order $order): bool
    {
        if ($this->orderRepository->hasPayments($order)) {
            return false;
        }

        return $this->orderRepository->delete($order);
    }

    public function canAcceptPayments(Order $order): bool
    {
        return $order->status === OrderStatus::CONFIRMED;
    }

}
