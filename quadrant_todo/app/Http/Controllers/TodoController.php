<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTodoRequest;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Requests\BulkCarryOverRequest;
use App\Http\Requests\CarryOverRequest;
use App\Http\Requests\PostponeRequest;
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
    public function index(ListTodoRequest $request): JsonResponse
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
   public function carryOver(CarryOverRequest $request, int $id): JsonResponse
{
    $todo = $this->todos->carryOver(
        $this->userId($request),
        $id,
        $request->validated('new_due_date'),
    );

    return ApiResponse::success(TodoResource::make($todo)->resolve());
}

//for a bulk carry over
public function bulkCarryOver(BulkCarryOverRequest $request): JsonResponse
{
    $items = $this->todos->carryOverDay(
        $this->userId($request),
        $request->validated('from_date'),
        $request->validated('new_due_date'),
    );

    return ApiResponse::success([
        'carried_over' => $items->count(),
        'items' => TodoResource::collection($items)->resolve(),
    ]);
}

//to postpone a todo
public function postpone(PostponeRequest $request, int $id): JsonResponse
{
    $todo = $this->todos->postpone(
        $this->userId($request),
        $id,
        $request->validated('new_due_date'),
    );

    return ApiResponse::success(TodoResource::make($todo)->resolve());
}
    //the private helper method for the userid
      private function userId(Request $request): int
    {
        return (int) $request->attributes->get('user_id');
    }
}