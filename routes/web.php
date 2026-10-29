<?php

use App\Http\Controllers\AuthorPageController;
use App\Http\Controllers\GenrePageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::controller(GenrePageController::class)
    ->prefix('genres')
    ->name('genres.page.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });

Route::controller(AuthorPageController::class)
    ->prefix('authors')
    ->name('authors.page.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });
