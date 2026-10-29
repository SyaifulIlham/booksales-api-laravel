<?php

use App\Models\Author;
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
        ->assertSee('Tambah genre');
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
        ->assertSee('Tambah author');
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
