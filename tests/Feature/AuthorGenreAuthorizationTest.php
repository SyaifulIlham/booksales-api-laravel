<?php

use App\Models\Author;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows guests to read author and genre lists and details through the API', function () {
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
});

it('rejects guests from author and genre API writes without changing records', function () {
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

it('forbids customers from author and genre API writes without changing records', function () {
    $author = Author::factory()->create();
    $genre = Genre::factory()->create();
    $customer = User::factory()->create();

    $this->actingAs($customer);

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

    $this->assertDatabaseCount('authors', 1);
    $this->assertDatabaseCount('genres', 1);
    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => $author->name]);
    $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => $genre->name]);
});
