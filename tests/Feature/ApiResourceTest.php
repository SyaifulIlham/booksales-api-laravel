<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates reads updates and deletes a book through the API', function () {
    $this->actingAs(User::factory()->admin()->create());

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

it('allows only admins to create update and delete books through the API', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();
    $book = Book::factory()->create();
    $attributes = [
        'title' => 'Restricted Book',
        'description' => 'Book description',
        'price' => 42.50,
        'stock' => 8,
        'cover_photo' => 'restricted-book.jpg',
        'genre_id' => $genre->id,
        'author_id' => $author->id,
    ];

    $this->postJson('/api/books', $attributes)->assertUnauthorized();
    $this->patchJson("/api/books/{$book->id}", ['title' => 'Changed Book'])->assertUnauthorized();
    $this->deleteJson("/api/books/{$book->id}")->assertUnauthorized();

    $this->actingAs(User::factory()->create());

    $this->postJson('/api/books', $attributes)->assertForbidden();
    $this->patchJson("/api/books/{$book->id}", ['title' => 'Changed Book'])->assertForbidden();
    $this->deleteJson("/api/books/{$book->id}")->assertForbidden();

    $this->assertDatabaseCount('books', 1);
    $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => $book->title]);
});

it('creates reads updates and deletes a genre through the API', function () {
    $this->actingAs(User::factory()->admin()->create());

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
    $this->actingAs(User::factory()->admin()->create());

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
    $this->actingAs(User::factory()->admin()->create());

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

it('requires a replacement when deleting authors and genres used by books', function () {
    $this->actingAs(User::factory()->admin()->create());

    $book = Book::factory()->create();

    $this->deleteJson("/api/authors/{$book->author_id}")
        ->assertConflict()
        ->assertJsonPath('message', 'Choose a replacement author before deleting an author used by books.');

    $this->deleteJson("/api/genres/{$book->genre_id}")
        ->assertConflict()
        ->assertJsonPath('message', 'Choose a replacement genre before deleting a genre used by books.');

    $this->assertModelExists($book->author);
    $this->assertModelExists($book->genre);
});

it('moves associated books before deleting their author and genre', function () {
    $this->actingAs(User::factory()->admin()->create());

    $genre = Genre::factory()->create(['name' => 'Genre to remove']);
    $replacementGenre = Genre::factory()->create(['name' => 'Replacement genre']);
    $author = Author::factory()->create(['name' => 'Author to remove']);
    $replacementAuthor = Author::factory()->create(['name' => 'Replacement author']);
    $book = Book::factory()->create([
        'genre_id' => $genre->id,
        'author_id' => $author->id,
    ]);

    $genreList = $this->getJson('/api/genres')->assertOk()->json();
    expect(collect($genreList)->firstWhere('id', $genre->id)['books_count'])->toBe(1);

    $authorList = $this->getJson('/api/authors')->assertOk()->json();
    expect(collect($authorList)->firstWhere('id', $author->id)['books_count'])->toBe(1);

    $this->deleteJson("/api/genres/{$genre->id}", ['replacement_id' => $replacementGenre->id])
        ->assertNoContent();
    $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'genre_id' => $replacementGenre->id,
    ]);

    $this->deleteJson("/api/authors/{$author->id}", ['replacement_id' => $replacementAuthor->id])
        ->assertNoContent();
    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'author_id' => $replacementAuthor->id,
    ]);
});

it('rejects the deleted author or genre as its own replacement', function () {
    $this->actingAs(User::factory()->admin()->create());

    $book = Book::factory()->create();

    $this->deleteJson("/api/genres/{$book->genre_id}", ['replacement_id' => $book->genre_id])
        ->assertUnprocessable()
        ->assertInvalid(['replacement_id']);

    $this->deleteJson("/api/authors/{$book->author_id}", ['replacement_id' => $book->author_id])
        ->assertUnprocessable()
        ->assertInvalid(['replacement_id']);

    $this->assertModelExists($book->genre);
    $this->assertModelExists($book->author);
});

it('returns not found when requesting a resource that does not exist', function () {
    $this->getJson('/api/books/999')
        ->assertNotFound();
});

it('returns 404 when requesting, updating, or deleting a missing genre or author', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->getJson('/api/genres/999')->assertNotFound();
    $this->patchJson('/api/genres/999', ['name' => 'Missing'])->assertNotFound();
    $this->deleteJson('/api/genres/999')->assertNotFound();

    $this->getJson('/api/authors/999')->assertNotFound();
    $this->patchJson('/api/authors/999', ['name' => 'Missing'])->assertNotFound();
    $this->deleteJson('/api/authors/999')->assertNotFound();
});
