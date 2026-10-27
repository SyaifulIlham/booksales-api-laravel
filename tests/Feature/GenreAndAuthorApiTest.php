<?php

use Database\Seeders\BookSalesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns five genres as JSON', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->getJson('/api/genres');

    $response->assertOk()
        ->assertJsonCount(5)
        ->assertJsonFragment(['name' => 'Fiction'])
        ->assertJsonFragment(['name' => 'Non-Fiction'])
        ->assertJsonFragment(['name' => 'Science Fiction'])
        ->assertJsonFragment(['name' => 'Romance'])
        ->assertJsonFragment(['name' => 'Mystery']);
});

it('returns five authors as JSON', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->getJson('/api/authors');

    $response->assertOk()
        ->assertJsonCount(5)
        ->assertJsonFragment(['name' => 'J.K. Rowling'])
        ->assertJsonFragment(['name' => 'George R.R. Martin'])
        ->assertJsonFragment(['name' => 'Isaac Asimov'])
        ->assertJsonFragment(['name' => 'Tere Liye'])
        ->assertJsonFragment(['name' => 'Andrea Hirata']);
});
