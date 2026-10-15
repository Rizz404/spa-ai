<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy extends SpaScopedPolicy
{
    public function update(User $user, Reservation $reservation): Response
    {
        return $this->ownsSpa($user, $reservation->spa_id);
    }
}
