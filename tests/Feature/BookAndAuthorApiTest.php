<?php

use Database\Seeders\BookSalesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns five seeded books with their authors and genres as JSON', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->getJson('/api/books');

    $response->assertOk()
        ->assertJsonCount(5)
        ->assertJsonFragment([
            'title' => "Harry Potter and the Philosopher's Stone",
        ])
        ->assertJsonFragment(['name' => 'J.K. Rowling'])
        ->assertJsonFragment(['title' => 'A Game of Thrones'])
        ->assertJsonFragment(['name' => 'George R.R. Martin']);
});
