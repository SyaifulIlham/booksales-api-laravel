<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10000, 250000),
            'stock' => fake()->numberBetween(0, 100),
            'cover_photo' => fake()->imageUrl(),
            'genre_id' => Genre::factory(),
            'author_id' => Author::factory(),
        ];
    }
}
