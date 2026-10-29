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
}
