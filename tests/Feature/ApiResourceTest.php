<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates reads updates and deletes a book through the API', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();

    $response = $this->postJson('/api/books', [
        'title' => 'API Book',
        'description' => 'Book description',
        'price' => 42.50,
        'stock' => 8,
        'cover_photo' => 'api-book.jpg',
        'genre_id' => $genre->id,
        'author_id' => $author->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('title', 'API Book')
        ->assertJsonPath('author.id', $author->id)
        ->assertJsonPath('genre.id', $genre->id);

    $book = Book::query()->findOrFail($response->json('id'));

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'API Book',
    ]);

    $this->getJson("/api/books/{$book->id}")
        ->assertOk()
        ->assertJsonPath('id', $book->id);

    $this->patchJson("/api/books/{$book->id}", [
        'title' => 'Updated API Book',
        'stock' => 3,
    ])->assertOk()
        ->assertJsonPath('title', 'Updated API Book')
        ->assertJsonPath('stock', 3);

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'Updated API Book',
        'stock' => 3,
    ]);

    $this->deleteJson("/api/books/{$book->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});

it('creates reads updates and deletes a genre through the API', function () {
    $response = $this->postJson('/api/genres', [
        'name' => 'New Genre',
        'description' => 'Genre description',
    ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'New Genre');

    $genreId = $response->json('id');

    $this->getJson("/api/genres/{$genreId}")
        ->assertOk()
        ->assertJsonPath('description', 'Genre description');

    $this->patchJson("/api/genres/{$genreId}", [
        'name' => 'Updated Genre',
    ])->assertOk()
        ->assertJsonPath('name', 'Updated Genre');

    $this->assertDatabaseHas('genres', [
        'id' => $genreId,
        'name' => 'Updated Genre',
    ]);

    $this->deleteJson("/api/genres/{$genreId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('genres', ['id' => $genreId]);
});

it('creates reads updates and deletes an author through the API', function () {
    $response = $this->postJson('/api/authors', [
        'name' => 'New Author',
        'photo' => 'new-author.jpg',
        'bio' => 'Author biography',
    ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'New Author');

    $authorId = $response->json('id');

    $this->getJson("/api/authors/{$authorId}")
        ->assertOk()
        ->assertJsonPath('bio', 'Author biography');

    $this->patchJson("/api/authors/{$authorId}", [
        'name' => 'Updated Author',
    ])->assertOk()
        ->assertJsonPath('name', 'Updated Author');

    $this->assertDatabaseHas('authors', [
        'id' => $authorId,
        'name' => 'Updated Author',
    ]);

    $this->deleteJson("/api/authors/{$authorId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('authors', ['id' => $authorId]);
});

it('rejects invalid book data without creating a book', function () {
    $this->postJson('/api/books', [
        'title' => '',
        'price' => -1,
        'stock' => -1,
        'genre_id' => 999,
        'author_id' => 999,
    ])->assertUnprocessable()
        ->assertInvalid([
            'title',
            'description',
            'price',
            'stock',
            'cover_photo',
            'genre_id',
            'author_id',
        ]);

    $this->assertDatabaseCount('books', 0);
});

it('prevents deleting authors and genres that are used by books', function () {
    $book = Book::factory()->create();

    $this->deleteJson("/api/authors/{$book->author_id}")
        ->assertConflict()
        ->assertJsonPath('message', 'Author cannot be deleted while books are associated with it.');

    $this->deleteJson("/api/genres/{$book->genre_id}")
        ->assertConflict()
        ->assertJsonPath('message', 'Genre cannot be deleted while books are associated with it.');

    $this->assertModelExists($book->author);
    $this->assertModelExists($book->genre);
});

it('returns not found when requesting a resource that does not exist', function () {
    $this->getJson('/api/books/999')
        ->assertNotFound();
});
