<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\Payments\{
    ProcessPaymentRequest,
    ReadPaymentRequest,
    ShowPaymentRequest,
    UpdatePaymentStatusRequest,
};
use App\Interfaces\Repositories\{
    OrderRepositoryInterface,
    PaymentRepositoryInterface,
};
use App\Services\PaymentService;
use App\Traits\{
    ApiResponse,
    TransactionLogging
};
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
use App\Models\Order;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    use ApiResponse, TransactionLogging;

    /**
     * PaymentController constructor
     *
     * @param PaymentService $paymentService
     * @param PaymentRepositoryInterface $paymentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private PaymentService $paymentService,
        private PaymentRepositoryInterface $paymentRepository,
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Display a listing of payments
     *
     * @param ReadPaymentRequest $request
     * @return JsonResponse
     */
    public function index(ReadPaymentRequest $request): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request) {
            $perPage = $request->get('per_page', 15);

            $payments = $this->paymentService->getAllPaymentsWithUser(auth()->id(), $perPage);
            return $this->paginated(PaymentResource::collection($payments), 'Payments retrieved successfully');
        }, 'Get payments by user id and statuses', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
        ]);
    }

    /**
     * Process payment for an order
     *
     * @param ProcessPaymentRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function processPayment(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request, $order) {
            $paymentData = $request->validated();
            $paymentMethod = $paymentData['payment_method'];
            unset($paymentData['payment_method']);

            $payment = $this->paymentService->processPayment($order, $paymentMethod, $paymentData);

            return $this->success(new PaymentResource($payment), 'Payment processed successfully', 201);
        }, 'Process payment for an order', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'order' => $order,
        ]);
    }

    /**
     * Display the specified payment
     *
     * @param ShowPaymentRequest $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(ShowPaymentRequest $request, Payment $payment): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request, $payment) {
            return $this->success(new PaymentResource($payment), 'Payment retrieved successfully');
        }, 'Get payment by id', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'payment' => $payment,
        ]);
    }

    /**
     * Get payments for a specific order
     *
     * @param Order $order
     * @return JsonResponse
     */
    public function getOrderPayments(Order $order): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($order) {
            $payments = $this->paymentService->getPaymentsForOrder($order);

            return $this->success(PaymentResource::collection($payments), 'Order payments retrieved successfully');
        }, 'Get order payments', [
            'user_id' => auth()->id(),
            'order' => $order,
        ]);
    }

    /**
     * Update the status of the specified payment
     *
     * @param UpdatePaymentStatusRequest $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function updateStatus(UpdatePaymentStatusRequest $request, Payment $payment): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request, $payment) {
            $payment = $this->paymentService->updatePaymentStatus($payment, PaymentStatus::from($request->status));
            return $this->success(PaymentResource::make($payment), 'Payment status updated successfully');
        }, 'Update payment status', [
            'user_id' => auth()->id(),
            'request' => $request->all(),
            'payment' => $payment,
        ]);
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): JsonResponse
    {
        return $this->surroundWithTransaction(function () {
            $methods = $this->paymentService->getAvailablePaymentMethods();

            return $this->success($methods, 'Available payment methods retrieved successfully');
        }, 'Get available payment methods', [
            'user_id' => auth()->id(),
        ]);
    }
}
