<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run()
    {
        Book::create(['title' => 'شاهنامه', 'price' => 60000, 'stock' => 5]);
        Book::create(['title' => 'بوستان سعدی', 'price' => 50000, 'stock' => 3]);
        Book::create(['title' => 'دیوان حافظ', 'price' => 70000, 'stock' => 2]);
    }
}
