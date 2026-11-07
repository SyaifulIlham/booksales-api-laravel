<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a customer with a unique username and hashed password', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Dewi Lestari',
        'username' => 'dewilestari',
        'email' => 'dewi@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Registrasi berhasil')
        ->assertJsonPath('user.name', 'Dewi Lestari')
        ->assertJsonPath('user.username', 'dewilestari')
        ->assertJsonPath('user.email', 'dewi@example.com')
        ->assertJsonPath('user.role', 'customer')
        ->assertJsonStructure(['token']);

    $user = User::query()->where('email', 'dewi@example.com')->firstOrFail();

    expect(Hash::check('secret-password', $user->password))->toBeTrue();
    expect($response->json('user'))->not->toHaveKey('password');
    $this->assertModelExists($user);
});

it('returns validation errors when required registration fields are missing', function () {
    $this->postJson('/api/register', [])
        ->assertUnprocessable()
        ->assertInvalid(['name', 'username', 'email', 'password']);
});

it('rejects invalid email and username formats', function () {
    $this->postJson('/api/register', [
        'name' => 'Dewi Lestari',
        'username' => 'dewi lestari',
        'email' => 'bukan-email',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])
        ->assertUnprocessable()
        ->assertInvalid(['username', 'email']);
});

it('rejects duplicate registration email and username values', function () {
    User::factory()->create([
        'username' => 'dewilestari',
        'email' => 'dewi@example.com',
    ]);

    $this->postJson('/api/register', [
        'name' => 'Dewi Lestari',
        'username' => 'dewilestari',
        'email' => 'dewi@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])
        ->assertUnprocessable()
        ->assertInvalid(['username', 'email']);
});

it('rejects passwords shorter than eight characters', function () {
    $this->postJson('/api/register', [
        'name' => 'Dewi Lestari',
        'username' => 'dewilestari',
        'email' => 'dewi@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])
        ->assertUnprocessable()
        ->assertInvalid(['password']);

    $this->assertDatabaseCount('users', 0);
});

it('rejects mismatched password confirmation and attempts to register as an admin', function () {
    $this->postJson('/api/register', [
        'name' => 'Dewi Lestari',
        'username' => 'dewilestari',
        'email' => 'dewi@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertInvalid(['password']);

    $this->postJson('/api/register', [
        'name' => 'Admin Palsu',
        'username' => 'adminpalsu',
        'email' => 'admin@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'role' => 'admin',
    ])
        ->assertUnprocessable()
        ->assertInvalid(['role']);

    $this->assertDatabaseCount('users', 0);
});
