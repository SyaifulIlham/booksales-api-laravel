<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves the React application shell at the home page', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});

it('redirects customer, catalog, and admin pages into the React application', function (string $path, string $page) {
    $this->get($path)
        ->assertRedirect(url('/').'/#/'.$page);
})->with([
    'customer login' => ['/login', 'login'],
    'customer registration' => ['/register', 'register'],
    'book catalog' => ['/books', 'books'],
    'genre catalog' => ['/genres', 'genres'],
    'author catalog' => ['/authors', 'authors'],
    'admin dashboard' => ['/admin', 'admin'],
]);

it('preserves the application subdirectory when redirecting to a React page', function () {
    $this->call('GET', '/booksales-api/public/login', [], [], [], [
        'SCRIPT_NAME' => '/booksales-api/public/index.php',
        'SCRIPT_FILENAME' => public_path('index.php'),
        'REQUEST_URI' => '/booksales-api/public/login',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => 80,
        'HTTP_HOST' => 'localhost',
    ])->assertRedirect(url('/').'/#/login');
});
