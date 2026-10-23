<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Contracts\View\View;

class GenreController extends Controller
{
    public function index(): View
    {
        return view('genres.index', [
            'genres' => Genre::data(),
        ]);
    }
}
