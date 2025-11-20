<?php
namespace App\Services;

use App\Models\Book;
use App\Models\Rental;
use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
USE App\Models\RentRule ;
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

            $days = $start->diffInDays($due) + 1;

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
     * Return rental: calculate fine based on rules and restock.
     */
    public function returnRental(Rental $rental, $returnedAt = null): Rental
    {
        return DB::transaction(function () use ($rental, $returnedAt) {

            if ($rental->status === 'returned') {
                throw new InvalidArgumentException('rental already returned');
            }

            $returned = $returnedAt ? Carbon::parse($returnedAt) : Carbon::now();
            $due = Carbon::parse($rental->due_date)->startOfDay();

            // اگر زودتر برگرداند → هیچ دیرکردی وجود ندارد
            if ($returned->lt($due)) {
                $lateDays = 0;
            } else {
                $lateDays = $due->diffInDays($returned->startOfDay());
            }


            // بارگذاری قانون جریمه دیرکرد فعال
            $lateFeeRule = RentRule::where('name', 'late_fee')->where('active', true)->first();

            $fine = 0;
            if ($lateDays > 0 && $lateFeeRule) {
                foreach ($rental->items as $item) {
                    switch ($lateFeeRule->value_type) {
                        case 'fixed':
                            // جریمه ثابت به ازای هر کتاب
                            $fine += $lateFeeRule->value * $item->quantity;
                            break;

                        case 'percent':
                            // درصدی از قیمت روزانه × تعداد روز × تعداد کتاب‌ها
                            $fine += $item->price_per_day * $item->quantity * $lateDays * ($lateFeeRule->value / 100);
                            break;

                        case 'day_rate':
                            // مبلغ ثابت روزانه × تعداد روز × تعداد کتاب‌ها
                            $fine += $lateFeeRule->value * $item->quantity * $lateDays;
                            break;
                    }
                }
            }

            // بازگرداندن موجودی کتاب‌ها
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
