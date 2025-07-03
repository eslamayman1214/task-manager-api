<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Util\HttpStatusCodeUtil;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for User Authentication"
 * )
 */
class AuthController extends Controller
{
    public function __construct(protected AuthService $service) {}

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->service->register($request->validated());

        return $this->response(new UserResource($user), HttpStatusCodeUtil::CREATED, 'User registered successfully!');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Authenticate user and retrieve token",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginRequest $request)
    {
        $data = $this->service->login($request->validated());

        if (! $data) {
            return $this->unauthorised();
        }

        return $this->response(['user' => new UserResource($data['user']), 'token' => $data['token']], HttpStatusCodeUtil::OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="User logged out successfully")
     * )
     */
    public function logout()
    {
        $this->service->logout();

        return $this->response([], HttpStatusCodeUtil::OK, 'User logged out successfully!');
    }
}
