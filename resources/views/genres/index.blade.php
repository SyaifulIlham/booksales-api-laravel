@extends('layouts.app')

@section('title', 'Genre')

@section('content')
    <h1>Daftar Genre</h1>
    <p>Semua data genre.</p>
    <p><a class="button" href="{{ route('genres.page.create') }}">Tambah genre</a></p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif
    @error('delete')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror

    <div class="cards">
        @foreach ($genres as $genre)
            <article class="card">
                <p class="muted">ID {{ $genre->id }}</p>
                <h2>{{ $genre->name }}</h2>
                <p>{{ $genre->description }}</p>
                <p>
                    <a href="{{ route('genres.page.show', $genre) }}">Detail</a>
                    <a href="{{ route('genres.page.edit', $genre) }}">Ubah</a>
                </p>
                <form method="POST" action="{{ route('genres.page.destroy', $genre) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button" type="submit">Hapus</button>
                </form>
            </article>
        @endforeach
    </div>

    @if ($genres->isEmpty())
        <p>Belum ada data genre.</p>
    @endif
@endsection
