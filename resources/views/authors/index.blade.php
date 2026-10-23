@extends('layouts.app')

@section('title', 'Author')

@section('content')
    <h1>Daftar Author</h1>
    <p>Lima data author dari Model Author.</p>

    <div class="cards">
        @foreach ($authors as $author)
            <article class="card">
                <p class="muted">ID {{ $author['id'] }}</p>
                <h2>{{ $author['name'] }}</h2>
                <p>{{ $author['bio'] }}</p>
            </article>
        @endforeach
    </div>
@endsection
