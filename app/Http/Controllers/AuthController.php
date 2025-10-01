<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\{
    LoginRequest,
    RegisterRequest
};
use App\Traits\{
    ApiResponse,
    TransactionLogging
};
use Illuminate\Http\JsonResponse;
use App\Services\UserService;
use App\Exceptions\UserException;

class AuthController extends Controller
{
    use TransactionLogging, ApiResponse;

    public function __construct(private UserService $userService)
    {
    }

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request) {
            $result = $this->userService->register($request->validated());
            return $this->success($result, 'User registered successfully', 201);
        }, 'Register a new user');
    }

    /**
     * Login user and return JWT token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->surroundWithTransaction(function () use ($request) {
            try {
                $result = $this->userService->login($request->validated());
                return $this->success($result, 'User logged in successfully', 200);
            } catch (UserException $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }, 'Login user and return JWT token');
    }

    /**
     * Get the authenticated user
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = $this->userService->me();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(['user' => $user], 'User profile retrieved successfully');
    }

    /**
     * Logout user (invalidate token)
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->surroundWithTransaction(function () {
            $this->userService->logout();
            return $this->success([], 'User logged out successfully');
        }, 'Logout user');
    }
}
