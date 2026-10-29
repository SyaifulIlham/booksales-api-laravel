<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GenrePageController extends Controller
{
    public function index(): View
    {
        $genres = Genre::query()
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
        return view('genres.show', compact('genre'));
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

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return back()->withErrors([
                'delete' => 'Genre tidak dapat dihapus selama masih digunakan oleh buku.',
            ]);
        }

        $genre->delete();

        return redirect()
            ->route('genres.page.index')
            ->with('status', 'Genre berhasil dihapus.');
    }
}
