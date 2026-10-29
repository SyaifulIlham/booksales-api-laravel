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

    public function show(Author $author): View
    {
        return view('authors.show', compact('author'));
    }

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
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

    public function update(Request $request, Author $author): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string'],
        ]);

        $author->update($validated);

        return redirect()
            ->route('authors.page.show', $author)
            ->with('status', 'Author berhasil diperbarui.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->books()->exists()) {
            return back()->withErrors([
                'delete' => 'Author tidak dapat dihapus selama masih digunakan oleh buku.',
            ]);
        }

        $author->delete();

        return redirect()
            ->route('authors.page.index')
            ->with('status', 'Author berhasil dihapus.');
    }
}
