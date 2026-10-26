<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    public function index(): View
    {
        return view('books.index', [
            'books' => Book::query()
                ->with(['author', 'genre'])
                ->orderBy('title')
                ->get(),
        ]);
    }
}
