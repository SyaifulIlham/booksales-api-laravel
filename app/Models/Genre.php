<?php

namespace App\Models;

class Genre
{
    /**
     * @return list<array{id: int, name: string, description: string}>
     */
    public static function data(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Fiction',
                'description' => 'Cerita rekaan dan imajinatif',
            ],
            [
                'id' => 2,
                'name' => 'Non-Fiction',
                'description' => 'Buku berbasis fakta dan pengetahuan',
            ],
            [
                'id' => 3,
                'name' => 'Science Fiction',
                'description' => 'Cerita bertema sains dan masa depan',
            ],
            [
                'id' => 5,
                'name' => 'Romance',
                'description' => 'Cerita bertema percintaan',
            ],
            [
                'id' => 6,
                'name' => 'Mystery',
                'description' => 'Cerita misteri dan teka-teki',
            ],
        ];
    }
}
