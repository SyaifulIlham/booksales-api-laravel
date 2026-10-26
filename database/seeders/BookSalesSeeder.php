<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class BookSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = [
            ['name' => 'J.K. Rowling', 'photo' => 'jk_rowling.jpg', 'bio' => 'Penulis seri Harry Potter asal Inggris.'],
            ['name' => 'George R.R. Martin', 'photo' => 'george_martin.jpg', 'bio' => 'Penulis novel fantasi A Song of Ice and Fire.'],
            ['name' => 'Isaac Asimov', 'photo' => 'isaac_asimov.jpg', 'bio' => 'Penulis fiksi ilmiah dan sains populer.'],
            ['name' => 'Tere Liye', 'photo' => 'tere_liye.jpg', 'bio' => 'Penulis novel fantasi dan drama asal Indonesia.'],
            ['name' => 'Andrea Hirata', 'photo' => 'andrea_hirata.jpg', 'bio' => 'Penulis novel Indonesia, lahir di Belitung.'],
        ];

        foreach ($authors as $authorData) {
            Author::query()->firstOrCreate(
                ['name' => $authorData['name']],
                $authorData,
            );
        }

        $genres = [
            ['name' => 'Fiction', 'description' => 'Cerita rekaan dan imajinatif.'],
            ['name' => 'Non-Fiction', 'description' => 'Buku berbasis fakta dan pengetahuan.'],
            ['name' => 'Science Fiction', 'description' => 'Cerita bertema sains dan masa depan.'],
            ['name' => 'Romance', 'description' => 'Cerita bertema percintaan.'],
            ['name' => 'Mystery', 'description' => 'Cerita misteri dan teka-teki.'],
        ];

        foreach ($genres as $genreData) {
            Genre::query()->firstOrCreate(
                ['name' => $genreData['name']],
                $genreData,
            );
        }

        $books = [
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'description' => 'Petualangan pertama Harry Potter di dunia sihir.',
                'price' => 150000,
                'stock' => 20,
                'cover_photo' => 'harry-potter.jpg',
                'author' => 'J.K. Rowling',
                'genre' => 'Fiction',
            ],
            [
                'title' => 'A Game of Thrones',
                'description' => 'Kisah perebutan takhta di Westeros.',
                'price' => 180000,
                'stock' => 15,
                'cover_photo' => 'a-game-of-thrones.jpg',
                'author' => 'George R.R. Martin',
                'genre' => 'Fiction',
            ],
            [
                'title' => 'Foundation',
                'description' => 'Kisah fiksi ilmiah tentang masa depan peradaban manusia.',
                'price' => 125000,
                'stock' => 12,
                'cover_photo' => 'foundation.jpg',
                'author' => 'Isaac Asimov',
                'genre' => 'Science Fiction',
            ],
            [
                'title' => 'Bumi',
                'description' => 'Petualangan Raib, Seli, dan Ali menjelajahi dunia paralel.',
                'price' => 95000,
                'stock' => 30,
                'cover_photo' => 'bumi.jpg',
                'author' => 'Tere Liye',
                'genre' => 'Fiction',
            ],
            [
                'title' => 'Laskar Pelangi',
                'description' => 'Kisah persahabatan dan perjuangan anak-anak Belitung.',
                'price' => 85000,
                'stock' => 25,
                'cover_photo' => 'laskar-pelangi.jpg',
                'author' => 'Andrea Hirata',
                'genre' => 'Fiction',
            ],
        ];

        foreach ($books as $bookData) {
            $author = Author::query()->where('name', $bookData['author'])->firstOrFail();
            $genre = Genre::query()->where('name', $bookData['genre'])->firstOrFail();

            Book::query()->firstOrCreate(
                ['title' => $bookData['title']],
                [
                    'description' => $bookData['description'],
                    'price' => $bookData['price'],
                    'stock' => $bookData['stock'],
                    'cover_photo' => $bookData['cover_photo'],
                    'author_id' => $author->id,
                    'genre_id' => $genre->id,
                ],
            );
        }
    }
}
