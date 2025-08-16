<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Api\Controllers\TodoController;
use App\Http\Api\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for Todos
Route::apiResource('todos', TodoController::class);

// Additional todo routes for user management
Route::post('/todos/{id}/assign-user', [TodoController::class, 'assignUser']);
Route::delete('/todos/{id}/remove-user', [TodoController::class, 'removeUser']);

// API Routes for Users
Route::apiResource('users', UserController::class);
Route::get('/users/{id}/todos', [UserController::class, 'todos']);
