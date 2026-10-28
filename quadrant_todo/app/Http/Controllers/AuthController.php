<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $auth): JsonResponse
    {
        $user = $auth->register(
            $request->validated('email'),
            $request->validated('password'),
        );

        return ApiResponse::success($user, 201);
    }

    public function login(LoginRequest $request, AuthService $auth): JsonResponse
    {
        $session = $auth->login(
            $request->validated('email'),
            $request->validated('password'),
        );

        return ApiResponse::success($session);
    }

    public function logout(Request $request, AuthService $auth): Response
    {
        $auth->logout($request->bearerToken());

        return ApiResponse::noContent();
    }
}