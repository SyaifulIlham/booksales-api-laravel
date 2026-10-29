<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - BookSales</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
        }

        body {
            margin: 0;
        }

        header {
            background: #1e3a5f;
            color: #fff;
        }

        nav, main {
            width: min(100% - 2rem, 960px);
            margin: 0 auto;
        }

        nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            min-height: 64px;
        }

        nav a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        main {
            padding: 2rem 0;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .card {
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 12px rgb(15 23 42 / 5%);
        }

        .card h2 {
            margin: 0 0 0.75rem;
            color: #1e3a5f;
            font-size: 1.15rem;
        }

        .card p {
            margin: 0;
            line-height: 1.6;
        }

        .muted {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .button {
            display: inline-block;
            padding: 0.65rem 1rem;
            border: 0;
            border-radius: 6px;
            background: #1e3a5f;
            color: #fff;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .form {
            display: grid;
            gap: 1rem;
            max-width: 640px;
            margin-top: 1.5rem;
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
        }

        .form-field {
            display: grid;
            gap: 0.4rem;
        }

        .form-field input,
        .form-field textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 0.65rem;
            border: 1px solid #9ca3af;
            border-radius: 6px;
            font: inherit;
        }

        .form-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .error,
        .status {
            margin-top: 0.5rem;
            color: #b91c1c;
        }

        .status {
            color: #166534;
        }
    </style>
</head>
<body>
    <header>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('home') }}">BookSales</a>
            <a href="{{ route('books.index') }}">Buku</a>
            <a href="{{ route('genres.page.index') }}">Genre</a>
            <a href="{{ route('authors.page.index') }}">Author</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
