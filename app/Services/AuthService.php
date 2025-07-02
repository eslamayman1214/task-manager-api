<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;

class AuthService
{
    public function __construct(protected AuthRepository $repository) {}

    public function register(array $data): User
    {
        return $this->repository->create($data);
    }

    public function login(array $credentials): array|bool
    {
        $user = $this->repository->validateCredentials($credentials);

        if (! $user) {
            return false;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    public function logout(): void
    {
        auth()->user()->currentAccessToken()->delete();
    }
}
