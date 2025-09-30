<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases()),
            'status' => $this->faker->randomElement(PaymentStatus::cases()),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'meta' => [
                'card_last_four' => $this->faker->numerify('####'),
                'cardholder_name' => $this->faker->name(),
            ],
            'transaction_id' => $this->faker->uuid(),
            'gateway_response' => [
                'status' => 'success',
                'message' => 'Payment processed successfully',
            ],
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::COMPLETED,
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the payment is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
        ]);
    }

    /**
     * Indicate that the payment method is credit card.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);
    }

    /**
     * Indicate that the payment method is bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::BANK_TRANSFER,
        ]);
    }
}
