@extends('layouts.app')

@section('title', 'Ubah Genre')

@section('content')
    <h1>Ubah Genre</h1>

    <form class="form" method="POST" action="{{ route('genres.page.update', $genre) }}">
        @csrf
        @method('PUT')

        <div class="form-field">
            <label for="name">Nama genre</label>
            <input id="name" name="name" type="text" value="{{ old('name', $genre->name) }}" required maxlength="255">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" required>{{ old('description', $genre->description) }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <button class="button" type="submit">Simpan perubahan</button>
            <a href="{{ route('genres.page.show', $genre) }}">Batal</a>
        </div>
    </form>
@endsection
