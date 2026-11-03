<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_number',
        'customer_id',
        'book_id',
        'quantity',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
