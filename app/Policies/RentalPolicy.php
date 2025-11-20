<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RentalPolicy
{
    public function view(User $user, Rental $rental)
    {
        return $rental->user_id === $user->id;
    }

    public function update(User $user, Rental $rental)
    {
        return $rental->user_id === $user->id;
    }
}
