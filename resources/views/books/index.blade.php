@extends('layouts.app')

@section('title', 'Buku')

@section('content')
    <h1>Daftar Buku</h1>
    <p>Lima data buku beserta author dan genre dari database.</p>

    <div class="cards">
        @foreach ($books as $book)
            <article class="card">
                <p class="muted">{{ $book->genre->name }}</p>
                <h2>{{ $book->title }}</h2>
                <p>{{ $book->description }}</p>
                <p>Author: {{ $book->author->name }}</p>
                <p>Harga: Rp {{ number_format((float) $book->price, 0, ',', '.') }}</p>
                <p>Stok: {{ $book->stock }}</p>
            </article>
        @endforeach
    </div>
@endsection
