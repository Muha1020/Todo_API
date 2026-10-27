<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiExceptions;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{


//Get all todos (filter by user_id)
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(['items' => [], 'page' => 1, 'per_page' => 20, 'total' => 0]);
    }

    //'POST' stores a new todo
    public function store(Request $request): JsonResponse
    {
        return ApiResponse::success(['placeholder' => 'store'], 201);
    }
    
    //detailed view of a todo
    public function show(int $id): JsonResponse
    {
        throw ApiExceptions::notFound();   // temporary, to test the 404 envelope
    }

    //'PATCH' to edit a todo
    public function update(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(['placeholder' => 'update']);
    }

    //to delete
    public function destroy(int $id): Response
    {
        return ApiResponse::noContent();
    }

    //to perform a cerryover
    public function carryOver(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(['placeholder' => 'carry-over']);
    }

    //to perform a  bulk carryover or multiple todos
    public function bulkCarryOver(Request $request): JsonResponse
    {
        return ApiResponse::success(['carried_over' => 0, 'items' => []]);
    }

    //to postpone a todo
    public function postpone(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(['placeholder' => 'postpone']);
    }
}