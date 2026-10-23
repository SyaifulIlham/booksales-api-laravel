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
    </style>
</head>
<body>
    <header>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('home') }}">BookSales</a>
            <a href="{{ route('genres.index') }}">Genre</a>
            <a href="{{ route('authors.index') }}">Author</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
