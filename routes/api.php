<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::apiResource('books', BookController::class);
Route::apiResource('genres', GenreController::class)->only(['index', 'show']);
Route::apiResource('authors', AuthorController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('genres', GenreController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('authors', AuthorController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'destroy']);
});

Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    Route::apiResource('transactions', TransactionController::class)
        ->only(['store', 'show', 'update']);
});
