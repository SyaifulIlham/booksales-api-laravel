<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        $genres = Genre::query()
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return response()->json($genres);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        return response()->json(Genre::query()->create($validated), 201);
    }

    public function show(Genre $genre): JsonResponse
    {
        return response()->json($genre);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ]);

        $genre->update($validated);

        return response()->json($genre);
    }

    public function destroy(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'replacement_id' => ['sometimes', 'required', 'integer', 'exists:genres,id'],
        ]);
        $replacementId = isset($validated['replacement_id']) ? (int) $validated['replacement_id'] : null;

        if ($replacementId === $genre->id) {
            throw ValidationException::withMessages([
                'replacement_id' => 'Choose a different genre for the associated books.',
            ]);
        }

        $deleted = DB::transaction(function () use ($genre, $replacementId): bool {
            $genre = Genre::query()->lockForUpdate()->findOrFail($genre->id);

            if ($genre->books()->exists() && $replacementId === null) {
                return false;
            }

            if ($replacementId !== null) {
                $genre->books()->update(['genre_id' => $replacementId]);
            }

            $genre->delete();

            return true;
        });

        if (! $deleted) {
            return response()->json([
                'message' => 'Choose a replacement genre before deleting a genre used by books.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
