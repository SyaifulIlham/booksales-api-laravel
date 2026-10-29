@extends('layouts.app')

@section('title', 'Tambah Genre')

@section('content')
    <h1>Tambah Genre</h1>

    <form class="form" method="POST" action="{{ route('genres.page.store') }}">
        @csrf

        <div class="form-field">
            <label for="name">Nama genre</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-field">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" required>{{ old('description') }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <button class="button" type="submit">Simpan genre</button>
            <a href="{{ route('genres.page.index') }}">Batal</a>
        </div>
    </form>
@endsection
