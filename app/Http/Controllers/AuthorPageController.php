<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthorPageController extends Controller
{
    public function index(): View
    {
        $authors = Author::query()
            ->withCount('books')
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
        $author->loadCount('books');
        $replacementAuthors = Author::query()
            ->where('id', '!=', $author->id)
            ->orderBy('name')
            ->get();

        return view('authors.show', compact('author', 'replacementAuthors'));
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

    public function destroy(Request $request, Author $author): RedirectResponse
    {
        $validated = $request->validate([
            'replacement_id' => ['sometimes', 'required', 'integer', 'exists:authors,id'],
        ]);
        $replacementId = isset($validated['replacement_id']) ? (int) $validated['replacement_id'] : null;

        if ($replacementId === $author->id) {
            throw ValidationException::withMessages([
                'replacement_id' => 'Pilih author pengganti yang berbeda.',
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
            return back()->withErrors([
                'delete' => 'Pilih author pengganti untuk memindahkan buku sebelum menghapus author ini.',
            ])->withInput();
        }

        return redirect()
            ->route('authors.page.index')
            ->with('status', 'Author berhasil dihapus.');
    }
}
