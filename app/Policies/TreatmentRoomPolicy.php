<?php

namespace App\Policies;

use App\Models\TreatmentRoom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TreatmentRoomPolicy extends SpaScopedPolicy
{
    public function update(User $user, TreatmentRoom $treatmentRoom): Response
    {
        return $this->ownsSpa($user, $treatmentRoom->spa_id);
    }

    public function delete(User $user, TreatmentRoom $treatmentRoom): Response
    {
        return $this->ownsSpa($user, $treatmentRoom->spa_id);
    }
}
