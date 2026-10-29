@extends('layouts.app')

@section('title', 'Genre')

@section('content')
    <h1>Daftar Genre</h1>
    <p>Semua data genre.</p>
    <p><a class="button" href="{{ route('genres.page.create') }}">Tambah genre</a></p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif

    <div class="cards">
        @foreach ($genres as $genre)
            <article class="card">
                <p class="muted">ID {{ $genre->id }}</p>
                <h2>{{ $genre->name }}</h2>
                <p>{{ $genre->description }}</p>
            </article>
        @endforeach
    </div>

    @if ($genres->isEmpty())
        <p>Belum ada data genre.</p>
    @endif
@endsection
