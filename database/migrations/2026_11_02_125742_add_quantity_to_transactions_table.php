<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1);
        });

        DB::table('transactions')
            ->join('books', 'books.id', '=', 'transactions.book_id')
            ->select([
                'transactions.id as transaction_id',
                'transactions.total_amount',
                'books.price',
            ])
            ->orderBy('transactions.id')
            ->chunkById(500, function (Collection $transactions): void {
                foreach ($transactions as $transaction) {
                    $totalInCents = (int) round((float) $transaction->total_amount * 100);
                    $priceInCents = (int) round((float) $transaction->price * 100);
                    $quantity = 1;

                    if ($priceInCents > 0 && $totalInCents > 0 && $totalInCents % $priceInCents === 0) {
                        $quantity = intdiv($totalInCents, $priceInCents);
                    }

                    DB::table('transactions')
                        ->where('id', $transaction->transaction_id)
                        ->update(['quantity' => $quantity]);
                }
            }, 'transactions.id', 'transaction_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
