<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenrePageController extends Controller
{
    public function index(): View
    {
        $genres = Genre::query()
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return view('genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function show(Genre $genre): View
    {
        $genre->loadCount('books');
        $replacementGenres = Genre::query()
            ->where('id', '!=', $genre->id)
            ->orderBy('name')
            ->get();

        return view('genres.show', compact('genre', 'replacementGenres'));
    }

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        Genre::query()->create($validated);

        return redirect()
            ->route('genres.page.index')
            ->with('status', 'Genre berhasil ditambahkan.');
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $genre->update($validated);

        return redirect()
            ->route('genres.page.show', $genre)
            ->with('status', 'Genre berhasil diperbarui.');
    }

    public function destroy(Request $request, Genre $genre): RedirectResponse
    {
        $validated = $request->validate([
            'replacement_id' => ['sometimes', 'required', 'integer', 'exists:genres,id'],
        ]);
        $replacementId = isset($validated['replacement_id']) ? (int) $validated['replacement_id'] : null;

        if ($replacementId === $genre->id) {
            throw ValidationException::withMessages([
                'replacement_id' => 'Pilih genre pengganti yang berbeda.',
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
            return back()->withErrors([
                'delete' => 'Pilih genre pengganti untuk memindahkan buku sebelum menghapus genre ini.',
            ])->withInput();
        }

        return redirect()
            ->route('genres.page.index')
            ->with('status', 'Genre berhasil dihapus.');
    }
}
