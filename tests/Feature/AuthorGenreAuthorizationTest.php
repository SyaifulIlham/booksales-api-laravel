<?php

use App\Models\Author;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows guests to read author and genre lists and details', function () {
    $author = Author::factory()->create(['name' => 'Public Author']);
    $genre = Genre::factory()->create(['name' => 'Public Genre']);

    $this->getJson('/api/authors')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Public Author']);

    $this->getJson("/api/authors/{$author->id}")
        ->assertOk()
        ->assertJsonPath('name', 'Public Author');

    $this->getJson('/api/genres')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Public Genre']);

    $this->getJson("/api/genres/{$genre->id}")
        ->assertOk()
        ->assertJsonPath('name', 'Public Genre');

    $this->get('/authors')->assertOk()->assertSee('Public Author');
    $this->get(route('authors.page.show', $author))->assertOk()->assertSee('Public Author');
    $this->get('/genres')->assertOk()->assertSee('Public Genre');
    $this->get(route('genres.page.show', $genre))->assertOk()->assertSee('Public Genre');
});

it('returns 401 to guests attempting author and genre API writes', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();

    $this->postJson('/api/authors', [
        'name' => 'New Author',
        'photo' => 'new-author.jpg',
        'bio' => 'Author biography',
    ])->assertUnauthorized();

    $this->patchJson("/api/authors/{$author->id}", ['name' => 'Changed Author'])
        ->assertUnauthorized();
    $this->deleteJson("/api/authors/{$author->id}")->assertUnauthorized();

    $this->postJson('/api/genres', [
        'name' => 'New Genre',
        'description' => 'Genre description',
    ])->assertUnauthorized();

    $this->patchJson("/api/genres/{$genre->id}", ['name' => 'Changed Genre'])
        ->assertUnauthorized();
    $this->deleteJson("/api/genres/{$genre->id}")->assertUnauthorized();

    $this->assertDatabaseCount('authors', 1);
    $this->assertDatabaseCount('genres', 1);
    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => $author->name]);
    $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => $genre->name]);
});

it('returns 401 to guests attempting author and genre web write routes', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();

    $this->get('/authors/create')->assertUnauthorized();
    $this->get(route('authors.page.edit', $author))->assertUnauthorized();
    $this->post('/authors', [])->assertUnauthorized();
    $this->put(route('authors.page.update', $author), [])->assertUnauthorized();
    $this->delete(route('authors.page.destroy', $author))->assertUnauthorized();

    $this->get('/genres/create')->assertUnauthorized();
    $this->get(route('genres.page.edit', $genre))->assertUnauthorized();
    $this->post('/genres', [])->assertUnauthorized();
    $this->put(route('genres.page.update', $genre), [])->assertUnauthorized();
    $this->delete(route('genres.page.destroy', $genre))->assertUnauthorized();
});

it('forbids non-admin users from author and genre API and web write routes', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->postJson('/api/authors', [
        'name' => 'New Author',
        'photo' => 'new-author.jpg',
        'bio' => 'Author biography',
    ])->assertForbidden();
    $this->patchJson("/api/authors/{$author->id}", ['name' => 'Changed Author'])
        ->assertForbidden();
    $this->deleteJson("/api/authors/{$author->id}")->assertForbidden();

    $this->postJson('/api/genres', [
        'name' => 'New Genre',
        'description' => 'Genre description',
    ])->assertForbidden();
    $this->patchJson("/api/genres/{$genre->id}", ['name' => 'Changed Genre'])
        ->assertForbidden();
    $this->deleteJson("/api/genres/{$genre->id}")->assertForbidden();

    $this->get('/authors/create')->assertForbidden();
    $this->get(route('authors.page.edit', $author))->assertForbidden();
    $this->post('/authors', [])->assertForbidden();
    $this->put(route('authors.page.update', $author), [])->assertForbidden();
    $this->delete(route('authors.page.destroy', $author))->assertForbidden();

    $this->get('/genres/create')->assertForbidden();
    $this->get(route('genres.page.edit', $genre))->assertForbidden();
    $this->post('/genres', [])->assertForbidden();
    $this->put(route('genres.page.update', $genre), [])->assertForbidden();
    $this->delete(route('genres.page.destroy', $genre))->assertForbidden();

    $this->assertDatabaseCount('authors', 1);
    $this->assertDatabaseCount('genres', 1);
    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => $author->name]);
    $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => $genre->name]);
});
