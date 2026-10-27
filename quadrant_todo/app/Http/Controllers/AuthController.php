<?php

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{

    //signup endpoint
    public function register(Request $request): JsonResponse
    {
        return ApiResponse::success(['id' => 1, 'email' => 'placeholder@example.com'], 201);
    }

    //login
    public function login(Request $request): JsonResponse
    {
        return ApiResponse::success(['token' => 'placeholder', 'token_type' => 'Bearer']);
    }

    //and finally logout!
    public function logout(Request $request): Response
    {
        return ApiResponse::noContent();
    }
}
