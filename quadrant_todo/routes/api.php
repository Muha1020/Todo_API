<?php

use App\Support\ApiResponse;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;


//test route
route::get('ping', fn () => ApiResponse::success([
'Kolawole is active?' => true,
'time' => now()->utc(),
'message' =>'this is how i want my test endpoint to read,too much play',
]));

//authentication endpoints
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

//todos endpoints
Route::prefix('todos')->middleware('auth.token')->group(function () {
    Route::get('/', [TodoController::class, 'index']);
    Route::post('/', [TodoController::class, 'store']);
    Route::post('carry-over', [TodoController::class, 'bulkCarryOver']);

    Route::get('{id}', [TodoController::class, 'show'])->whereNumber('id');
    Route::patch('{id}', [TodoController::class, 'update'])->whereNumber('id');
    Route::delete('{id}', [TodoController::class, 'destroy'])->whereNumber('id');
    Route::post('{id}/carry-over', [TodoController::class, 'carryOver'])->whereNumber('id');
    Route::post('{id}/postpone', [TodoController::class, 'postpone'])->whereNumber('id');
});