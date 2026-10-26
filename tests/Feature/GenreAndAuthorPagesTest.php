<?php

use Database\Seeders\BookSalesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders five genres from the model', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->get('/genres');

    $response->assertOk()
        ->assertViewHas('genres', fn (Collection $genres): bool => $genres->count() === 5)
        ->assertSee('Fiction')
        ->assertSee('Non-Fiction')
        ->assertSee('Science Fiction')
        ->assertSee('Romance')
        ->assertSee('Mystery');
});

it('renders five authors from the model', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->get('/authors');

    $response->assertOk()
        ->assertViewHas('authors', fn (Collection $authors): bool => $authors->count() === 5)
        ->assertSee('J.K. Rowling')
        ->assertSee('George R.R. Martin')
        ->assertSee('Isaac Asimov')
        ->assertSee('Tere Liye')
        ->assertSee('Andrea Hirata');
});
