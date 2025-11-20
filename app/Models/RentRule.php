<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentRule extends Model
{
     protected $table = 'rent_rules'; 

    protected $fillable = [
        'name',
        'description',
        'value',
        'value_type',
        'active',
    ];
}
