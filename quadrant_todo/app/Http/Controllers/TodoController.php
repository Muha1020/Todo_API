<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTodosRequest;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Services\TodoService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{

 public function __construct(private TodoService $todos) {}

//Get all todos (filter by user_id)
    public function index(ListTodosRequest $request): JsonResponse
    {
        $result = $this->todos->list(
            $this->userId($request),
            $request->validated(),
            $request->integer('page', 1),
            $request->integer('per_page', 20),
        );

        return ApiResponse::success([
            'items' => TodoResource::collection($result->items())->resolve(),
            'page' => $result->currentPage(),
            'per_page' => $result->perPage(),
            'total' => $result->total(),
        ]);
    }

    //'POST' stores a new todo
     public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = $this->todos->create($this->userId($request), $request->validated());

        return ApiResponse::success(TodoResource::make($todo)->resolve(), 201);
    }
    
    //detailed view of a todo
     public function show(Request $request, int $id): JsonResponse
    {
        $todo = $this->todos->get($this->userId($request), $id);

        return ApiResponse::success(TodoResource::make($todo)->resolve());
    }

    //'PATCH' to edit a todo
     public function update(UpdateTodoRequest $request, int $id): JsonResponse
    {
        $todo = $this->todos->update($this->userId($request), $id, $request->validated());

        return ApiResponse::success(TodoResource::make($todo)->resolve());
    }

    //to delete
     public function destroy(Request $request, int $id): Response
    {
        $this->todos->delete($this->userId($request), $id);

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

    //the private helper method for the userid
      private function userId(Request $request): int
    {
        return (int) $request->attributes->get('user_id');
    }
}