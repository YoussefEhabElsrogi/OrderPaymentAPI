<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register()
    {
        $payload = [
            'name' => 'Youssef Elsrogi',
            'email' => 'youssef@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_in'
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'youssef@example.com']);
    }

    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'youssef@example.com']);

        $payload = [
            'name' => 'Another User',
            'email' => 'youssef@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422);
    }

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_in'
                ]
            ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword')
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_view_profile()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        // Login to get a valid token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email']
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        // Login to get a valid token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User logged out successfully'
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_protected_endpoints()
    {
        // Test /api/auth/me endpoint
        $response = $this->getJson('/api/auth/me');
        $response->assertStatus(401);

        // Test /api/auth/logout endpoint
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401);
    }
}
