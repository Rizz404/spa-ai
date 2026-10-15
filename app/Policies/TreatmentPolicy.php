<?php

namespace App\Policies;

use App\Models\Treatment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TreatmentPolicy extends SpaScopedPolicy
{
    public function update(User $user, Treatment $treatment): Response
    {
        return $this->ownsSpa($user, $treatment->spa_id);
    }

    public function delete(User $user, Treatment $treatment): Response
    {
        return $this->ownsSpa($user, $treatment->spa_id);
    }
}
