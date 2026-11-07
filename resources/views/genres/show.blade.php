@extends('layouts.app')

@section('title', 'Detail Genre')

@section('content')
    <h1>{{ $genre->name }}</h1>
    <p class="muted">ID {{ $genre->id }}</p>
    <p>{{ $genre->description }}</p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif
    @error('delete')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror

    <p>
        <a class="button" href="{{ route('genres.page.edit', $genre) }}">Ubah genre</a>
        <a href="{{ route('genres.page.index') }}">Kembali</a>
    </p>
    <form method="POST" action="{{ route('genres.page.destroy', $genre) }}">
        @csrf
        @method('DELETE')
        @if ($genre->books_count > 0)
            @if ($replacementGenres->isNotEmpty())
                <label>
                    Pindahkan buku ke genre
                    <select name="replacement_id" required>
                        <option value="">Pilih genre pengganti</option>
                        @foreach ($replacementGenres as $replacementGenre)
                            <option value="{{ $replacementGenre->id }}">{{ $replacementGenre->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <p>Tambahkan genre lain sebagai tujuan pemindahan buku terlebih dahulu.</p>
            @endif
        @endif
        @if ($genre->books_count === 0 || $replacementGenres->isNotEmpty())
            <button class="button" type="submit">Hapus genre</button>
        @endif
    </form>
@endsection
