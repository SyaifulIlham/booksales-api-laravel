<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'price',
        'stock',
        'cover_photo',
        'genre_id',
        'author_id',
    ];

    protected $appends = [
        'cover_photo',
    ];

    protected $hidden = [
        'cover_foto',
    ];

    protected function coverPhoto(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $attributes['cover_foto'] ?? $value,
            set: fn (mixed $value): array => [
                Schema::hasColumn($this->getTable(), 'cover_foto') ? 'cover_foto' : 'cover_photo' => $value,
            ],
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
