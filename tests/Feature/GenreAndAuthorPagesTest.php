<?php

it('renders five genres from the model', function () {
    $response = $this->get('/genres');

    $response->assertOk()
        ->assertViewHas('genres', fn (array $genres): bool => count($genres) === 5)
        ->assertSee('Fiction')
        ->assertSee('Non-Fiction')
        ->assertSee('Science Fiction')
        ->assertSee('Romance')
        ->assertSee('Mystery');
});

it('renders five authors from the model', function () {
    $response = $this->get('/authors');

    $response->assertOk()
        ->assertViewHas('authors', fn (array $authors): bool => count($authors) === 5)
        ->assertSee('J.K. Rowling')
        ->assertSee('George R.R. Martin')
        ->assertSee('Isaac Asimov')
        ->assertSee('Tere Liye')
        ->assertSee('Andrea Hirata');
});
