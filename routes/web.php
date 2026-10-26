<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
