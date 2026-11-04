<?php

it('renders the React admin panel at the site root', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});
