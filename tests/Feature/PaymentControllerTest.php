<?php

namespace Tests\Feature;

use App\Enums\{
    OrderStatus,
    PaymentStatus,
};
use App\Models\{
    Order,
    Payment,
    User,
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_get_their_payments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);
        Payment::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'order_id',
                        'order',
                        'payment_method',
                        'status',
                        'amount',
                        'meta',
                        'transaction_id',
                        'gateway_response',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta',
                'links'
            ]);
    }

    #[Test]
    public function authenticated_user_can_process_payment_for_confirmed_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::CONFIRMED,
            'total_amount' => 100.00
        ]);

        $payload = [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'cardholder_name' => 'John Doe',
            'expiry_month' => '12',
            'expiry_year' => '2025',
            'cvv' => '123'
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'order_id',
                    'order' => [
                        'id',
                        'user',
                        'items'
                    ],
                    'payment_method',
                    'status',
                    'amount',
                    'meta',
                    'transaction_id',
                    'gateway_response',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'amount' => 100.00
        ]);
    }

    #[Test]
    public function authenticated_user_can_view_specific_payment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id]);

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'order_id',
                    'order',
                    'payment_method',
                    'status',
                    'amount',
                    'meta',
                    'transaction_id',
                    'gateway_response',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_can_get_payments_for_specific_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);
        Payment::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->getJson("/api/orders/{$order->id}/payments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'order_id',
                        'order',
                        'payment_method',
                        'status',
                        'amount',
                        'meta',
                        'transaction_id',
                        'gateway_response',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_payment_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::PENDING
        ]);

        $payload = [
            'status' => 'completed'
        ];

        $response = $this->patchJson("/api/payments/{$payment->id}/status", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed'
        ]);
    }

    #[Test]
    public function authenticated_user_can_get_available_payment_methods()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/payments/methods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    #[Test]
    public function user_cannot_process_payment_for_pending_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
            'total_amount' => 100.00
        ]);

        $payload = [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'cardholder_name' => 'John Doe',
            'expiry_month' => '12',
            'expiry_year' => '2025',
            'cvv' => '123'
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $payload);

        $response->assertStatus(400);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_payments()
    {
        $response = $this->getJson('/api/payments');
        $response->assertStatus(401);
    }

    #[Test]
    public function user_cannot_access_other_user_payments()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $otherUserOrder = Order::factory()->create(['user_id' => $otherUser->id]);
        $otherUserPayment = Payment::factory()->create(['order_id' => $otherUserOrder->id]);

        $response = $this->getJson("/api/payments/{$otherUserPayment->id}");
        $response->assertStatus(403);
    }

    #[Test]
    public function payment_fails_with_invalid_card_details()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::CONFIRMED,
            'total_amount' => 50.00
        ]);

        $payload = [
            'payment_method' => 'credit_card',
            'card_number' => '1234567890123456', // invalid Luhn
            'cardholder_name' => 'John Doe',
            'expiry_month' => '12',
            'expiry_year' => '2025',
            'cvv' => '123'
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $payload);

        $response->assertStatus(400);
    }

    #[Test]
    public function payment_fails_with_unsupported_payment_method()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::CONFIRMED,
            'total_amount' => 50.00
        ]);

        $payload = [
            'payment_method' => 'apple_pay', // unsupported
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $payload);

        $response->assertStatus(422);
    }

    #[Test]
    public function update_status_does_not_change_completed_payment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::COMPLETED
        ]);

        $payload = [
            'status' => 'completed'
        ];

        $response = $this->patchJson("/api/payments/{$payment->id}/status", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed'
        ]);
    }
}
