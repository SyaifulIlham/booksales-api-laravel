<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
})->name('home');

Route::get('/{path}', function (Request $request, string $path) {
    if (str_starts_with($path, 'api/')) {
        abort(404);
    }

    $applicationPath = rtrim($request->getBaseUrl(), '/');

    return redirect()->to($request->getSchemeAndHttpHost().$applicationPath.'/#/'.ltrim($path, '/'));
})->where('path', '.*');
