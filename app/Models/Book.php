<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
      protected $fillable = ['title','author','price_per_day','stock'];

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }
}
