<?php

namespace App\Interfaces\Services;

use App\Http\Resources\UserResource;

interface UserServiceInterface
{
    public function register(array $data): array;
    public function login(array $credentials): array;
    public function me(): ?UserResource;
    public function logout(): void;
}
