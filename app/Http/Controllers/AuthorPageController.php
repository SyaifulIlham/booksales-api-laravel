<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorPageController extends Controller
{
    public function index(): View
    {
        $authors = Author::query()
            ->orderBy('name')
            ->get();

        return view('authors.index', compact('authors'));
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string'],
        ]);

        Author::query()->create($validated);

        return redirect()
            ->route('authors.page.index')
            ->with('status', 'Author berhasil ditambahkan.');
    }
}
