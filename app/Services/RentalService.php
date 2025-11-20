<?php
namespace App\Services;

use App\Models\Book;
use App\Models\Rental;
use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RentalService
{
    protected float $fineRate; // fine rate is percent of price_per_day per late day

    public function __construct()
    {
        $this->fineRate = config('rentals.fine_rate', 0.1);
    }

    public function createRental(array $payload): Rental
    {
        return DB::transaction(function () use ($payload) {
            $start = Carbon::parse($payload['start_date'])->startOfDay();
            $due = Carbon::parse($payload['due_date'])->startOfDay();
            if ($due->lt($start)) {
                throw new InvalidArgumentException('due_date must be >= start_date');
            }

            $days = $due->diffInDays($start) ?: 1; // if same day => 1 day

            $rental = Rental::create([
                'user_id' => $payload['user_id'],
                'start_date' => $start->toDateString(),
                'due_date' => $due->toDateString(),
                'status' => 'active',
            ]);

            $total = 0;

            foreach ($payload['items'] as $it) {
                $book = Book::lockForUpdate()->findOrFail($it['book_id']);
                if ($book->stock < $it['quantity']) {
                    throw new InvalidArgumentException("کتاب {$book->title} کافی نیست");
                }
                // deduct stock
                $book->stock -= $it['quantity'];
                $book->save();

                $pricePerDay = $book->price_per_day;
                $subtotal = $pricePerDay * $days * $it['quantity'];

                $rental->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $it['quantity'],
                    'price_per_day' => $pricePerDay,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $rental->total_price = $total;
            $rental->save();

            return $rental->load('items.book');
        });
    }

    /**
     * Return rental: calculate fine and restock.
     */
    public function returnRental(Rental $rental, ?\DateTimeInterface $returnedAt = null): Rental
    {
        return DB::transaction(function () use ($rental, $returnedAt) {
            if ($rental->status === 'returned') {
                throw new InvalidArgumentException('rental already returned');
            }
            $returned = $returnedAt ? Carbon::parse($returnedAt) : Carbon::now();
            $due = Carbon::parse($rental->due_date)->startOfDay();
            $lateDays = max(0, $returned->startOfDay()->diffInDays($due, false));

            $fine = 0;
            if ($lateDays > 0) {
                foreach ($rental->items as $item) {
                    // fine per item = price_per_day * quantity * lateDays * fineRate
                    $fine += $item->price_per_day * $item->quantity * $lateDays * $this->fineRate;
                }
            }

            // restock
            foreach ($rental->items as $item) {
                $book = Book::lockForUpdate()->find($item->book_id);
                $book->stock += $item->quantity;
                $book->save();
            }

            $rental->returned_at = $returned;
            $rental->fine_amount = round($fine, 2);
            $rental->status = 'returned';
            $rental->save();

            return $rental->refresh()->load('items.book');
        });
    }
}
