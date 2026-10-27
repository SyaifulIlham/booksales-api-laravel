<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(): JsonResponse
    {
        $authors = Author::query()
            ->orderBy('name')
            ->get();

        return response()->json($authors);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string'],
        ]);

        return response()->json(Author::query()->create($validated), 201);
    }

    public function show(Author $author): JsonResponse
    {
        return response()->json($author);
    }

    public function update(Request $request, Author $author): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'photo' => ['sometimes', 'required', 'string', 'max:255'],
            'bio' => ['sometimes', 'required', 'string'],
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    public function destroy(Author $author): JsonResponse
    {
        if ($author->books()->exists()) {
            return response()->json([
                'message' => 'Author cannot be deleted while books are associated with it.',
            ], 409);
        }

        $author->delete();

        return response()->json(null, 204);
    }
}
