<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $books = Book::query()
            ->with(['author', 'genre'])
            ->orderBy('title')
            ->get();

        return response()->json($books);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'cover_photo' => ['required', 'string', 'max:255'],
            'genre_id' => ['required', 'integer', 'exists:genres,id'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
        ]);

        $book = Book::query()->create($validated)->load(['author', 'genre']);

        return response()->json($book, 201);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json($book->load(['author', 'genre']));
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'cover_photo' => ['sometimes', 'required', 'string', 'max:255'],
            'genre_id' => ['sometimes', 'required', 'integer', 'exists:genres,id'],
            'author_id' => ['sometimes', 'required', 'integer', 'exists:authors,id'],
        ]);

        $book->update($validated);

        return response()->json($book->load(['author', 'genre']));
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
