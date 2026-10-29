@extends('layouts.app')

@section('title', 'Ubah Author')

@section('content')
    <h1>Ubah Author</h1>

    <form class="form" method="POST" action="{{ route('authors.page.update', $author) }}">
        @csrf
        @method('PUT')

        <div class="form-field">
            <label for="name">Nama author</label>
            <input id="name" name="name" type="text" value="{{ old('name', $author->name) }}" required maxlength="255">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="photo">Foto author</label>
            <input id="photo" name="photo" type="text" value="{{ old('photo', $author->photo) }}" required maxlength="255">
            @error('photo')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="bio">Biografi</label>
            <textarea id="bio" name="bio" required>{{ old('bio', $author->bio) }}</textarea>
            @error('bio')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <button class="button" type="submit">Simpan perubahan</button>
            <a href="{{ route('authors.page.show', $author) }}">Batal</a>
        </div>
    </form>
@endsection
