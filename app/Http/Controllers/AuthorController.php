<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        return view('authors.index', [
            'authors' => Author::data(),
        ]);
    }
}
