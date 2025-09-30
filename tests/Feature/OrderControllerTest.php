<?php

namespace Tests\Feature;

use App\Enums\{
    PaymentStatus,
    OrderStatus,
};
use App\Models\{
    Order,
    Payment,
    User,
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_get_their_orders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'notes',
                        'total_amount',
                        'status',
                        'user',
                        'items',
                        'payments',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to'
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_can_get_orders_by_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::PENDING]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::CONFIRMED]);

        $response = $this->getJson('/api/orders?status=pending');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);
    }

    #[Test]
    public function authenticated_user_can_create_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'notes' => 'Test order',
            'items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 2,
                    'price' => 50.00,
                    'description' => 'Test product 1'
                ],
                [
                    'product_name' => 'Product 2',
                    'quantity' => 1,
                    'price' => 30.00,
                    'description' => 'Test product 2'
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'notes',
                    'total_amount',
                    'status',
                    'user',
                    'items',
                    'payments',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'notes' => 'Test order',
            'total_amount' => 130.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Product 1',
            'quantity' => 2,
            'price' => 50.00
        ]);
    }

    #[Test]
    public function authenticated_user_can_view_specific_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'notes',
                    'total_amount',
                    'status',
                    'user',
                    'items',
                    'payments',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $payload = [
            'notes' => 'Updated order notes'
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'notes',
                    'total_amount',
                    'status',
                    'user',
                    'items',
                    'payments',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'notes' => 'Updated order notes'
        ]);
    }

    #[Test]
    public function authenticated_user_can_update_order_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::PENDING]);

        $payload = [
            'status' => 'confirmed'
        ];

        $response = $this->patchJson("/api/orders/{$order->id}/status", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'notes',
                    'total_amount',
                    'status',
                    'user',
                    'items',
                    'payments',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);
    }

    #[Test]
    public function authenticated_user_can_delete_order_without_payments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Order deleted successfully'
            ]);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    #[Test]
    public function authenticated_user_cannot_delete_order_with_payments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'status' => PaymentStatus::COMPLETED
        ]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
                'message' => 'Cannot delete order with existing payments'
            ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);

        $response = $this->postJson('/api/orders', []);
        $response->assertStatus(401);
    }

    #[Test]
    public function user_cannot_update_order_of_another_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $payload = ['notes' => 'Hacked update'];

        $response = $this->putJson("/api/orders/{$order->id}", $payload);

        $response->assertStatus(403);
    }

    #[Test]
    public function order_index_respects_pagination()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Order::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/orders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1);
    }

    #[Test]
    public function order_status_update_fails_with_invalid_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $payload = ['status' => 'invalid_status'];

        $response = $this->patchJson("/api/orders/{$order->id}/status", $payload);

        $response->assertStatus(422); // validation error
    }
}
