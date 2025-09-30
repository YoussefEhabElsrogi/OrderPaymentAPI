<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $this->faker->randomFloat(2, 5, 200);

        return [
            'order_id' => Order::factory(),
            'product_name' => $this->faker->words(2, true),
            'quantity' => $quantity,
            'price' => $price,
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    /**
     * Create order item with specific order
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Create order item with specific product name
     */
    public function withProduct(string $productName): static
    {
        return $this->state(fn (array $attributes) => [
            'product_name' => $productName,
        ]);
    }

    /**
     * Create order item with specific price range
     */
    public function withPriceRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, $min, $max),
        ]);
    }
}
