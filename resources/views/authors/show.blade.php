@extends('layouts.app')

@section('title', 'Detail Author')

@section('content')
    <h1>{{ $author->name }}</h1>
    <p class="muted">ID {{ $author->id }}</p>
    <p>Foto: {{ $author->photo }}</p>
    <p>{{ $author->bio }}</p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif
    @error('delete')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror

    <p>
        <a class="button" href="{{ route('authors.page.edit', $author) }}">Ubah author</a>
        <a href="{{ route('authors.page.index') }}">Kembali</a>
    </p>
    <form method="POST" action="{{ route('authors.page.destroy', $author) }}">
        @csrf
        @method('DELETE')
        @if ($author->books_count > 0)
            @if ($replacementAuthors->isNotEmpty())
                <label>
                    Pindahkan buku ke author
                    <select name="replacement_id" required>
                        <option value="">Pilih author pengganti</option>
                        @foreach ($replacementAuthors as $replacementAuthor)
                            <option value="{{ $replacementAuthor->id }}">{{ $replacementAuthor->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <p>Tambahkan author lain sebagai tujuan pemindahan buku terlebih dahulu.</p>
            @endif
        @endif
        @if ($author->books_count === 0 || $replacementAuthors->isNotEmpty())
            <button class="button" type="submit">Hapus author</button>
        @endif
    </form>
@endsection
