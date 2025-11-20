<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\User;
use App\Models\Book;
use Carbon\Carbon;

class RentalSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $book1 = Book::first();
        $book2 = Book::skip(1)->first();

        $rental = Rental::create([
            'user_id' => $user->id,
            'start_date' => Carbon::now()->subDays(3),
            'due_date' => Carbon::now()->addDays(2),
            'total_price' => 0,
            'status' => 'active',
        ]);

        RentalItem::create([
            'rental_id' => $rental->id,
            'book_id' => $book1->id,
            'quantity' => 1,
            'price_per_day' => $book1->price_per_day,
            'subtotal' => 1 * $book1->price_per_day,

        ]);

        RentalItem::create([
            'rental_id' => $rental->id,
            'book_id' => $book2->id,
            'quantity' => 1,
            'price_per_day' => $book2->price_per_day,
            'subtotal' => 1 * $book2->price_per_day,

        ]);
    }
}
