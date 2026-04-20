<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User CRUD routes
    Route::get('/users', [AuthController::class, 'index']);
    Route::get('/users/{user}', [AuthController::class, 'show']);
    Route::put('/users/{user}', [AuthController::class, 'update']);
    Route::delete('/users/{user}', [AuthController::class, 'destroy']);
});
