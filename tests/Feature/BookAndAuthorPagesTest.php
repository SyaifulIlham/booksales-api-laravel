<?php

use App\Models\Book;
use Database\Seeders\BookSalesSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders five seeded books with their authors and genres', function () {
    $this->seed(BookSalesSeeder::class);

    $response = $this->get('/books');

    $response->assertOk()
        ->assertViewHas('books', fn (Collection $books): bool => $books->count() === 5
            && $books->every(fn (Book $book): bool => $book->relationLoaded('author')
                && $book->relationLoaded('genre')))
        ->assertSee("Harry Potter and the Philosopher's Stone")
        ->assertSee('J.K. Rowling')
        ->assertSee('A Game of Thrones')
        ->assertSee('George R.R. Martin');
});
