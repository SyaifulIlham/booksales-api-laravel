@extends('layouts.app')

@section('title', 'Data Buku')

@section('content')
    <h1>Data Buku, Genre, dan Author</h1>
    <p>Pilih menu untuk melihat data katalog buku.</p>

    <div class="cards">
        <section class="card">
            <h2>Buku</h2>
            <p>Lihat daftar buku beserta author dan genre.</p>
            <p><a href="{{ route('books.index') }}">Tampilkan buku</a></p>
        </section>
        <section class="card">
            <h2>Genre</h2>
            <p>Lihat dan tambahkan genre buku.</p>
            <p><a href="{{ route('genres.page.index') }}">Tampilkan genre</a></p>
        </section>
        <section class="card">
            <h2>Author</h2>
            <p>Lihat dan tambahkan author buku.</p>
            <p><a href="{{ route('authors.page.index') }}">Tampilkan author</a></p>
        </section>
    </div>
@endsection
