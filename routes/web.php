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
        Route::get('/{genre}', 'show')->name('show');
        Route::get('/{genre}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{genre}', 'update')->name('update');
        Route::delete('/{genre}', 'destroy')->name('destroy');
    });

Route::controller(AuthorPageController::class)
    ->prefix('authors')
    ->name('authors.page.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{author}', 'show')->name('show');
        Route::get('/{author}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{author}', 'update')->name('update');
        Route::delete('/{author}', 'destroy')->name('destroy');
    });
