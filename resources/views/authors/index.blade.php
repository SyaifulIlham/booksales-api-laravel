@extends('layouts.app')

@section('title', 'Author')

@section('content')
    <h1>Daftar Author</h1>
    <p>Semua data author.</p>
    <p><a class="button" href="{{ route('authors.page.create') }}">Tambah author</a></p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif
    @error('delete')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror

    <div class="cards">
        @foreach ($authors as $author)
            <article class="card">
                <p class="muted">ID {{ $author->id }}</p>
                <h2>{{ $author->name }}</h2>
                <p>Foto: {{ $author->photo }}</p>
                <p>{{ $author->bio }}</p>
                <p>
                    <a href="{{ route('authors.page.show', $author) }}">Detail</a>
                    <a href="{{ route('authors.page.edit', $author) }}">Ubah</a>
                </p>
                <form method="POST" action="{{ route('authors.page.destroy', $author) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button" type="submit">Hapus</button>
                </form>
            </article>
        @endforeach
    </div>

    @if ($authors->isEmpty())
        <p>Belum ada data author.</p>
    @endif
@endsection
