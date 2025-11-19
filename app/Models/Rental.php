<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
     protected $fillable = ['user_id','start_date','due_date','returned_at','status','total_price','fine_amount'];

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
