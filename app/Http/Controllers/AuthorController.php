<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthorController extends Controller
{
    public function index(): JsonResponse
    {
        $authors = Author::query()
            ->withCount('books')
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

    public function destroy(Request $request, Author $author): JsonResponse
    {
        $validated = $request->validate([
            'replacement_id' => ['sometimes', 'required', 'integer', 'exists:authors,id'],
        ]);
        $replacementId = isset($validated['replacement_id']) ? (int) $validated['replacement_id'] : null;

        if ($replacementId === $author->id) {
            throw ValidationException::withMessages([
                'replacement_id' => 'Choose a different author for the associated books.',
            ]);
        }

        $deleted = DB::transaction(function () use ($author, $replacementId): bool {
            $author = Author::query()->lockForUpdate()->findOrFail($author->id);

            if ($author->books()->exists() && $replacementId === null) {
                return false;
            }

            if ($replacementId !== null) {
                $author->books()->update(['author_id' => $replacementId]);
            }

            $author->delete();

            return true;
        });

        if (! $deleted) {
            return response()->json([
                'message' => 'Choose a replacement author before deleting an author used by books.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
