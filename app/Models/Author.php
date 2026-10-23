<?php

namespace App\Models;

class Author
{
    /**
     * @return list<array{id: int, name: string, foto: string, bio: string}>
     */
    public static function data(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'J.K. Rowling',
                'foto' => 'jk_rowling.jpg',
                'bio' => 'Penulis seri Harry Potter asal Inggris.',
            ],
            [
                'id' => 2,
                'name' => 'George R.R. Martin',
                'foto' => 'george_martin.jpg',
                'bio' => 'Penulis novel fantasi A Song of Ice and Fire.',
            ],
            [
                'id' => 3,
                'name' => 'Isaac Asimov',
                'foto' => 'isaac_asimov.jpg',
                'bio' => 'Penulis fiksi ilmiah dan sains populer.',
            ],
            [
                'id' => 4,
                'name' => 'Tere Liye',
                'foto' => 'tere_liye.jpg',
                'bio' => 'Penulis novel fantasi dan drama asal Indonesia.',
            ],
            [
                'id' => 5,
                'name' => 'Andrea Hirata',
                'foto' => 'andrea_hirata.jpg',
                'bio' => 'Penulis novel Indonesia, lahir di Belitung.',
            ],
        ];
    }
}
