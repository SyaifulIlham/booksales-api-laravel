<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'photo',
        'bio',
    ];

    protected $appends = [
        'photo',
    ];

    protected $hidden = [
        'foto',
    ];

    protected function photo(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $attributes['foto'] ?? $value,
            set: fn (mixed $value): array => [
                Schema::hasColumn($this->getTable(), 'foto') ? 'foto' : 'photo' => $value,
            ],
        );
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
