@extends('layouts.app')

@section('title', 'Tambah Author')

@section('content')
    <h1>Tambah Author</h1>

    <form class="form" method="POST" action="{{ route('authors.page.store') }}">
        @csrf

        <div class="form-field">
            <label for="name">Nama author</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="photo">Foto author</label>
            <input id="photo" name="photo" type="text" value="{{ old('photo') }}" required maxlength="255">
            @error('photo')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="bio">Biografi</label>
            <textarea id="bio" name="bio" required>{{ old('bio') }}</textarea>
            @error('bio')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <button class="button" type="submit">Simpan author</button>
            <a href="{{ route('authors.page.index') }}">Batal</a>
        </div>
    </form>
@endsection
