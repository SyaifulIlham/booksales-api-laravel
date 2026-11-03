<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::query()
            ->with(['customer', 'book'])
            ->orderByDesc('id')
            ->get();

        return response()->json($transactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:50', 'unique:transactions,order_number'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'customer_id' => ['prohibited'],
            'total_amount' => ['prohibited'],
        ]);

        $book = Book::query()->findOrFail($validated['book_id']);
        $user = $request->user();

        abort_unless($user !== null, 401);

        $transaction = $user->transactions()->create([
            'order_number' => $validated['order_number'],
            'book_id' => $book->id,
            'quantity' => $validated['quantity'],
            'total_amount' => $this->calculateTotalAmount($book, $validated['quantity']),
        ]);

        return response()->json($transaction->load(['customer', 'book']), 201);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $this->ensureCustomerOwnsTransaction($request, $transaction);

        return response()->json($transaction->load(['customer', 'book']));
    }

    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $this->ensureCustomerOwnsTransaction($request, $transaction);

        $validated = $request->validate([
            'order_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('transactions', 'order_number')->ignore($transaction->id),
            ],
            'book_id' => ['sometimes', 'required', 'integer', 'exists:books,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000000'],
            'customer_id' => ['prohibited'],
            'total_amount' => ['prohibited'],
        ]);

        if (array_key_exists('book_id', $validated) || array_key_exists('quantity', $validated)) {
            $book = array_key_exists('book_id', $validated)
                ? Book::query()->findOrFail($validated['book_id'])
                : $transaction->book;
            $quantity = $validated['quantity'] ?? $transaction->quantity;

            $validated['total_amount'] = $this->calculateTotalAmount($book, $quantity);
        }

        $transaction->update($validated);

        return response()->json($transaction->load(['customer', 'book']));
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json(null, 204);
    }

    private function ensureCustomerOwnsTransaction(Request $request, Transaction $transaction): void
    {
        $user = $request->user();

        abort_unless(
            $user !== null && (string) $transaction->customer_id === (string) $user->getAuthIdentifier(),
            404,
        );
    }

    private function calculateTotalAmount(Book $book, int $quantity): string
    {
        $priceInCents = (int) round((float) $book->price * 100);

        if ($priceInCents < 0 || ($priceInCents > 0 && $quantity > intdiv(9_999_999_999, $priceInCents))) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah buku menghasilkan total transaksi yang melebihi batas.',
            ]);
        }

        $totalInCents = $priceInCents * $quantity;

        return intdiv($totalInCents, 100).'.'.str_pad((string) ($totalInCents % 100), 2, '0', STR_PAD_LEFT);
    }
}
