<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows all genres on the genre page', function () {
    foreach (range(1, 6) as $number) {
        Genre::query()->create([
            'name' => "Genre {$number}",
            'description' => "Description {$number}",
        ]);
    }

    $response = $this->get('/genres');

    $response->assertOk()
        ->assertSee('Genre 1')
        ->assertSee('Genre 6')
        ->assertSee('Tambah genre')
        ->assertSee('Detail')
        ->assertSee('Ubah')
        ->assertSee('Hapus');
});

it('shows all authors on the author page', function () {
    foreach (range(1, 6) as $number) {
        Author::query()->create([
            'name' => "Author {$number}",
            'photo' => "author-{$number}.jpg",
            'bio' => "Biography {$number}",
        ]);
    }

    $response = $this->get('/authors');

    $response->assertOk()
        ->assertSee('Author 1')
        ->assertSee('Author 6')
        ->assertSee('author-1.jpg')
        ->assertSee('Tambah author')
        ->assertSee('Detail')
        ->assertSee('Ubah')
        ->assertSee('Hapus');
});

it('shows genre details and an edit form', function () {
    $genre = Genre::factory()->create([
        'name' => 'Adventure',
        'description' => 'Petualangan seru.',
    ]);

    $this->get(route('genres.page.show', $genre))
        ->assertOk()
        ->assertSee('Adventure')
        ->assertSee('Petualangan seru.');

    $this->get(route('genres.page.edit', $genre))
        ->assertOk()
        ->assertSee('value="Adventure"', false)
        ->assertSee('Petualangan seru.');
});

it('updates and deletes a genre from its page', function () {
    $genre = Genre::factory()->create();

    $this->put(route('genres.page.update', $genre), [
        'name' => 'Updated Genre',
        'description' => 'Updated description.',
    ])->assertRedirect(route('genres.page.show', $genre))
        ->assertSessionHas('status', 'Genre berhasil diperbarui.');

    $this->assertDatabaseHas('genres', [
        'id' => $genre->id,
        'name' => 'Updated Genre',
        'description' => 'Updated description.',
    ]);

    $this->delete(route('genres.page.destroy', $genre))
        ->assertRedirect(route('genres.page.index'))
        ->assertSessionHas('status', 'Genre berhasil dihapus.');

    $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
});

it('shows author details and an edit form', function () {
    $author = Author::factory()->create([
        'name' => 'Ayu Utami',
        'photo' => 'ayu.jpg',
        'bio' => 'Penulis Indonesia.',
    ]);

    $this->get(route('authors.page.show', $author))
        ->assertOk()
        ->assertSee('Ayu Utami')
        ->assertSee('Penulis Indonesia.');

    $this->get(route('authors.page.edit', $author))
        ->assertOk()
        ->assertSee('value="Ayu Utami"', false)
        ->assertSee('value="ayu.jpg"', false)
        ->assertSee('Penulis Indonesia.');
});

it('updates and deletes an author from its page', function () {
    $author = Author::factory()->create();

    $this->put(route('authors.page.update', $author), [
        'name' => 'Updated Author',
        'photo' => 'updated.jpg',
        'bio' => 'Updated biography.',
    ])->assertRedirect(route('authors.page.show', $author))
        ->assertSessionHas('status', 'Author berhasil diperbarui.');

    $this->assertDatabaseHas('authors', [
        'id' => $author->id,
        'name' => 'Updated Author',
        'photo' => 'updated.jpg',
        'bio' => 'Updated biography.',
    ]);

    $this->delete(route('authors.page.destroy', $author))
        ->assertRedirect(route('authors.page.index'))
        ->assertSessionHas('status', 'Author berhasil dihapus.');

    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

it('returns 404 for missing genre and author page records', function () {
    $this->get('/genres/999')->assertNotFound();
    $this->get('/authors/999')->assertNotFound();

    $this->put('/genres/999', [
        'name' => 'Missing',
        'description' => 'Missing',
    ])->assertNotFound();
    $this->put('/authors/999', [
        'name' => 'Missing',
        'photo' => 'missing.jpg',
        'bio' => 'Missing',
    ])->assertNotFound();

    $this->delete('/genres/999')->assertNotFound();
    $this->delete('/authors/999')->assertNotFound();
});

it('shows the genre creation form', function () {
    $response = $this->get('/genres/create');

    $response->assertOk()
        ->assertSee('Tambah Genre')
        ->assertSee('name="description"', false);
});

it('shows the author creation form', function () {
    $response = $this->get('/authors/create');

    $response->assertOk()
        ->assertSee('Tambah Author')
        ->assertSee('name="photo"', false)
        ->assertSee('name="bio"', false);
});

it('creates a genre from the genre form', function () {
    $response = $this->post('/genres', [
        'name' => 'Adventure',
        'description' => 'Cerita petualangan.',
    ]);

    $response->assertRedirect(route('genres.page.index'))
        ->assertSessionHas('status', 'Genre berhasil ditambahkan.');

    $this->assertDatabaseHas('genres', [
        'name' => 'Adventure',
        'description' => 'Cerita petualangan.',
    ]);
});

it('requires genre fields when creating a genre', function () {
    $response = $this->from('/genres/create')->post('/genres', []);

    $response->assertRedirect('/genres/create')
        ->assertSessionHasErrors(['name', 'description']);
    $this->assertDatabaseCount('genres', 0);
});

it('creates an author from the author form', function () {
    $response = $this->post('/authors', [
        'name' => 'Ayu Utami',
        'photo' => 'ayu-utami.jpg',
        'bio' => 'Penulis Indonesia.',
    ]);

    $response->assertRedirect(route('authors.page.index'))
        ->assertSessionHas('status', 'Author berhasil ditambahkan.');

    $this->assertDatabaseHas('authors', [
        'name' => 'Ayu Utami',
        'photo' => 'ayu-utami.jpg',
        'bio' => 'Penulis Indonesia.',
    ]);
});

it('requires author fields when creating an author', function () {
    $response = $this->from('/authors/create')->post('/authors', []);

    $response->assertRedirect('/authors/create')
        ->assertSessionHasErrors(['name', 'photo', 'bio']);
    $this->assertDatabaseCount('authors', 0);
});

it('prevents deleting genres and authors used by books', function () {
    $book = Book::factory()->create();

    $this->from(route('genres.page.show', $book->genre))
        ->delete(route('genres.page.destroy', $book->genre))
        ->assertSessionHasErrors(['delete']);
    $this->assertModelExists($book->genre);

    $this->from(route('authors.page.show', $book->author))
        ->delete(route('authors.page.destroy', $book->author))
        ->assertSessionHasErrors(['delete']);
    $this->assertModelExists($book->author);
});
