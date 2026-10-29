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
        <button class="button" type="submit">Hapus genre</button>
    </form>
@endsection
