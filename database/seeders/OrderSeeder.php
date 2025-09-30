<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users from database
        $users = User::all();

        // Create 100 orders using existing users
        $orders = collect();
        for ($i = 0; $i < 100; $i++) {
            $randomUser = $users->random();
            $order = Order::factory()->create([
                'user_id' => $randomUser->id,
            ]);
            $orders->push($order);
        }

        // Create order items for each order (1-5 items per order)
        foreach ($orders as $order) {
            $itemCount = fake()->numberBetween(1, 5);
            $totalAmount = 0;

            for ($i = 0; $i < $itemCount; $i++) {
                $quantity = fake()->numberBetween(1, 10);
                $price = fake()->randomFloat(2, 5, 200);
                $itemTotal = $quantity * $price;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                $totalAmount += $itemTotal;
            }

            // Update order total amount
            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
