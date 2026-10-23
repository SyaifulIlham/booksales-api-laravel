@extends('layouts.app')

@section('title', 'Data Buku')

@section('content')
    <h1>Data Genre dan Author</h1>
    <p>Pilih menu untuk melihat data yang disediakan oleh Model.</p>

    <div class="cards">
        <section class="card">
            <h2>Genre</h2>
            <p>Lihat lima genre buku.</p>
            <p><a href="{{ route('genres.index') }}">Tampilkan genre</a></p>
        </section>
        <section class="card">
            <h2>Author</h2>
            <p>Lihat lima author buku.</p>
            <p><a href="{{ route('authors.index') }}">Tampilkan author</a></p>
        </section>
    </div>
@endsection
