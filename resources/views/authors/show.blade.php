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
        <button class="button" type="submit">Hapus author</button>
    </form>
@endsection
