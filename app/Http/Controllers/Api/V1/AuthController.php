<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Util\HttpStatusCodeUtil;

class AuthController extends Controller
{
    public function __construct(protected AuthService $service) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->service->register($request->validated());

        return $this->response(new UserResource($user), HttpStatusCodeUtil::CREATED, 'User registered successfully!');
    }

    public function login(LoginRequest $request)
    {
        $data = $this->service->login($request->validated());

        if (! $data) {
            return $this->unauthorised();
        }

        return $this->response(['user' => new UserResource($data['user']), 'token' => $data['token']], HttpStatusCodeUtil::OK);
    }
}
