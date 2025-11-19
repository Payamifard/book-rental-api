<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalItem extends Model
{
      protected $fillable = ['rental_id','book_id','quantity','price_per_day','subtotal'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
