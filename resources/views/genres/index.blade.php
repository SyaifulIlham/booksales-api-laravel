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
                @php($replacementGenres = $genres->where('id', '!=', $genre->id))
                <p>
                    <a href="{{ route('genres.page.show', $genre) }}">Detail</a>
                    <a href="{{ route('genres.page.edit', $genre) }}">Ubah</a>
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
                        <button class="button" type="submit">Hapus</button>
                    @endif
                </form>
            </article>
        @endforeach
    </div>

    @if ($genres->isEmpty())
        <p>Belum ada data genre.</p>
    @endif
@endsection
