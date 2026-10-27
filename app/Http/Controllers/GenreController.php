<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        $genres = Genre::query()
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

    public function destroy(Genre $genre): JsonResponse
    {
        if ($genre->books()->exists()) {
            return response()->json([
                'message' => 'Genre cannot be deleted while books are associated with it.',
            ], 409);
        }

        $genre->delete();

        return response()->json(null, 204);
    }
}
