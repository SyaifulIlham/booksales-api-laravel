<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a customer to create a transaction with a server-calculated total', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $book = Book::factory()->create(['price' => 25000]);

    $this->actingAs($customer)
        ->postJson('/api/transactions', [
            'order_number' => 'ORD-TEST-001',
            'book_id' => $book->id,
            'quantity' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('order_number', 'ORD-TEST-001')
        ->assertJsonPath('quantity', 2)
        ->assertJsonPath('customer.id', $customer->id)
        ->assertJsonPath('book.id', $book->id)
        ->assertJsonPath('total_amount', '50000.00');

    $this->assertDatabaseHas('transactions', [
        'order_number' => 'ORD-TEST-001',
        'customer_id' => $customer->id,
        'book_id' => $book->id,
        'quantity' => 2,
        'total_amount' => '50000.00',
    ]);

    $this->assertDatabaseMissing('transactions', [
        'order_number' => 'ORD-TEST-001',
        'customer_id' => $otherCustomer->id,
    ]);
});

it('allows a customer to show and update only their own transaction', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $transaction = $customer->transactions()->create([
        'order_number' => 'ORD-TEST-002',
        'book_id' => Book::factory()->create(['price' => 25000])->id,
        'quantity' => 1,
        'total_amount' => '25000.00',
    ]);
    $otherTransaction = $otherCustomer->transactions()->create([
        'order_number' => 'ORD-TEST-003',
        'book_id' => Book::factory()->create(['price' => 15000])->id,
        'quantity' => 1,
        'total_amount' => '15000.00',
    ]);
    $newBook = Book::factory()->create(['price' => 12000]);

    $this->actingAs($customer)
        ->getJson("/api/transactions/{$transaction->id}")
        ->assertOk()
        ->assertJsonPath('customer.id', $customer->id)
        ->assertJsonPath('book.id', $transaction->book_id);

    $this->actingAs($customer)
        ->patchJson("/api/transactions/{$transaction->id}", [
            'book_id' => $newBook->id,
            'quantity' => 3,
        ])
        ->assertOk()
        ->assertJsonPath('book.id', $newBook->id)
        ->assertJsonPath('quantity', 3)
        ->assertJsonPath('total_amount', '36000.00');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'customer_id' => $customer->id,
        'book_id' => $newBook->id,
        'quantity' => 3,
        'total_amount' => '36000.00',
    ]);

    $this->getJson("/api/transactions/{$otherTransaction->id}")->assertNotFound();
    $this->patchJson("/api/transactions/{$otherTransaction->id}", [
        'order_number' => 'ORD-CHANGED',
    ])->assertNotFound();
});

it('returns only the authenticated customer transactions for their account page', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $transaction = $customer->transactions()->create([
        'order_number' => 'ORD-MINE-001',
        'book_id' => Book::factory()->create()->id,
        'quantity' => 1,
        'total_amount' => '12000.00',
    ]);
    $otherCustomer->transactions()->create([
        'order_number' => 'ORD-MINE-002',
        'book_id' => Book::factory()->create()->id,
        'quantity' => 2,
        'total_amount' => '24000.00',
    ]);

    $this->actingAs($customer)
        ->getJson('/api/my-transactions')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $transaction->id)
        ->assertJsonPath('0.order_number', 'ORD-MINE-001')
        ->assertJsonPath('0.book.id', $transaction->book_id);
});

it('allows only admins to list all transactions and delete transactions', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    $transaction = $customer->transactions()->create([
        'order_number' => 'ORD-TEST-004',
        'book_id' => Book::factory()->create()->id,
        'quantity' => 1,
        'total_amount' => '100.00',
    ]);

    $this->actingAs($admin)
        ->getJson('/api/transactions')
        ->assertOk()
        ->assertJsonPath('0.customer.id', $customer->id)
        ->assertJsonPath('0.book.id', $transaction->book_id);

    $this->deleteJson("/api/transactions/{$transaction->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('rejects guests and users with the wrong role from transaction routes', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $transaction = $customer->transactions()->create([
        'order_number' => 'ORD-TEST-005',
        'book_id' => Book::factory()->create()->id,
        'quantity' => 1,
        'total_amount' => '100.00',
    ]);

    $this->postJson('/api/transactions', [])->assertUnauthorized();
    $this->getJson('/api/transactions')->assertUnauthorized();
    $this->getJson("/api/transactions/{$transaction->id}")->assertUnauthorized();
    $this->patchJson("/api/transactions/{$transaction->id}", [])->assertUnauthorized();
    $this->deleteJson("/api/transactions/{$transaction->id}")->assertUnauthorized();

    $this->actingAs($admin)
        ->postJson('/api/transactions', [
            'order_number' => 'ORD-ADMIN-001',
            'book_id' => Book::factory()->create()->id,
            'quantity' => 1,
        ])
        ->assertForbidden();

    $this->getJson("/api/transactions/{$transaction->id}")->assertForbidden();
    $this->patchJson("/api/transactions/{$transaction->id}", [])->assertForbidden();

    $this->actingAs($customer)
        ->getJson('/api/transactions')
        ->assertForbidden();

    $this->deleteJson("/api/transactions/{$transaction->id}")->assertForbidden();
});

it('rejects invalid quantities and customer-supplied ownership or totals', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $book = Book::factory()->create(['price' => 25000]);

    $this->actingAs($customer)
        ->postJson('/api/transactions', [
            'order_number' => 'ORD-TEST-006',
            'book_id' => $book->id,
            'quantity' => 0,
        ])
        ->assertUnprocessable()
        ->assertInvalid(['quantity']);

    $this->postJson('/api/transactions', [
        'order_number' => 'ORD-TEST-007',
        'book_id' => $book->id,
        'quantity' => 1,
        'customer_id' => $otherCustomer->id,
    ])->assertUnprocessable()
        ->assertInvalid(['customer_id']);

    $this->postJson('/api/transactions', [
        'order_number' => 'ORD-TEST-008',
        'book_id' => $book->id,
        'quantity' => 1,
        'total_amount' => 1,
    ])->assertUnprocessable()
        ->assertInvalid(['total_amount']);

    $this->assertDatabaseCount('transactions', 0);
});
