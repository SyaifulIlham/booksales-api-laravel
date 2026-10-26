@extends('layouts.app')

@section('title', 'Genre')

@section('content')
    <h1>Daftar Genre</h1>
    <p>Lima data genre dari Model Genre.</p>

    <div class="cards">
        @foreach ($genres as $genre)
            <article class="card">
                <p class="muted">ID {{ $genre->id }}</p>
                <h2>{{ $genre->name }}</h2>
                <p>{{ $genre->description }}</p>
            </article>
        @endforeach
    </div>
@endsection
