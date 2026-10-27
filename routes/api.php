<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

Route::apiResources([
    'books' => BookController::class,
    'genres' => GenreController::class,
    'authors' => AuthorController::class,
]);
