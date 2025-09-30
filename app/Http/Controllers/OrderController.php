<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Http\Requests\
{
    StoreOrderRequest,
    UpdateOrderRequest,
    ReadOrderRequest,
    UpdateOrderStatusRequest,
    DeleteOrderRequest,
};
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Traits\
{
    ApiResponse,
    TransactionLogging,
};
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponse, TransactionLogging;

    public function __construct(
        private OrderService $orderService,
        private OrderRepositoryInterface $orderRepositoryInterface
    ) {
    }

    /**
     * Display a listing of orders
     *
     * @param ReadOrderRequest $request
     * @return JsonResponse
     */
    public function index(ReadOrderRequest $request): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request) {
            $orders = $this->orderService->getOrdersByUserIdAndStatus(auth()->id(), $request->get('per_page', 15), $request->get('status', null));
            return $this->paginated(OrderResource::collection($orders), 'Orders retrieved successfully');
        }, 'Get orders by user id and statuses', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
        ]);
    }

    /**
     * Store a newly created order
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request) {
            $order = $this->orderService->store($request->except('items'), $request->items);
            return $this->success(OrderResource::make($order), 'Order created successfully', 201);
        }, 'Create a new order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
        ]);
    }

    /**
     * Display the specified order
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        $order = $this->orderService->loadRelations($order);
        return $this->success(OrderResource::make($order), 'Order retrieved successfully');
    }

    /**
     * Update the specified order
     *
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request, $order) {

            $order = $this->orderService->updateOrder($order, $request->except('items'), $request->items ?? []);

            return $this->success(OrderResource::make($order), 'Order updated successfully');
        }, 'Update order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'order' => $order,
        ]);
    }

    /**
     * Update the status of the specified order
     *
     * @param UpdateOrderStatusRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request, $order) {
            $order = $this->orderService->updateStatus($order, OrderStatus::from($request->status));
            return $this->success(OrderResource::make($order), 'Order status updated successfully');
        }, 'Update order status', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'order' => $order,
        ]);
    }

    /**
     * Remove the specified order
     *
     * @param DeleteOrderRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function destroy(DeleteOrderRequest $request, Order $order): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($order) {
            $deleted = $this->orderService->delete($order);

            if (!$deleted) {
                return $this->error('Cannot delete order with existing payments', 400);
            }

            return $this->success([], 'Order deleted successfully');
        }, 'Delete order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'order' => $order,
        ]);
    }
}
